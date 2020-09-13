<?php


namespace App\Handler\MessageHandler\Slack;


use App\Entity\Slack\ModalSubmissionDto;
use App\Entity\Slack\SlackMessage;
use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Mail\Mailer;
use App\ObjectFactories\DailySummaryFactory;use App\Repository\DailySummaryRepository;
use App\Services\DatabaseHelper;
use App\Services\Time;
use App\Slack\SlackMessageHelper;

class DailySummaryHandler
{

    private DailySummaryRepository $dailySummaryRepo;

    private Time $time;

    private PunchTimerHandler $punchTimerHandler;

    private DailySummaryFactory $dailySummaryFactory;

    private DatabaseHelper $databaseHelper;

    private Mailer $mailer;

    private SlackMessageHelper $slackMessageHelper;

    public function __construct(
        PunchTimerHandler $punchTimerHandler,
        DailySummaryRepository $dailySummaryRepo,
        Time $time,
        DailySummaryFactory $dailySummaryFactory,
        DatabaseHelper $databaseHelper,
        Mailer $mailer,
        SlackMessageHelper $slackMessageHelper
    )
    {
        $this->dailySummaryRepo = $dailySummaryRepo;
        $this->time = $time;
        $this->punchTimerHandler = $punchTimerHandler;
        $this->dailySummaryFactory = $dailySummaryFactory;
        $this->databaseHelper = $databaseHelper;
        $this->mailer = $mailer;
        $this->slackMessageHelper = $slackMessageHelper;
    }

    public function getDailySummarySubmitView(string $slackTriggerId): array
    {
        return [
            'trigger_id' => $slackTriggerId,
            'view' => [
                'type' => 'modal',
                'callback_id' => 'ml_ds',
                'title' => [
                    'type' => 'plain_text',
                    'text' => 'Daily Summary'
                ],
                'submit' => [
                    'type' => 'plain_text',
                    'text' => 'Send'
                ],
                'blocks' => [
                    [
                        'type' => 'input',
                        'block_id' => 'daily_summary_block',
                        'element' => [
                            'type' => 'plain_text_input',
                            'action_id' => 'summary_block_input',
                            'multiline' => true,
                            'placeholder' => [
                                'type' => 'plain_text',
                                'text' => 'Add the tasks your completed here...'
                            ]
                        ],
                        'label' => [
                            'type' => 'plain_text',
                            'text' => 'Tasks'
                        ]
                    ],
                    [
                        'type' => 'input',
                        'block_id' => 'mail_block',
                        'label' => [
                            'type' => 'plain_text',
                            'text' => 'Send E-Mail?',
                            'emoji' => true
                        ],
                        'element' => [
                            'type' => 'static_select',
                            'action_id' => 'mail_choice',
                            'placeholder' => [
                                'type' => 'plain_text',
                                'text' => 'Send E-Mail?',
                                'emoji' => true
                            ],
                            'initial_option' => [
                                'text' => [
                                    'type' => 'plain_text',
                                    'text' => ':heavy_check_mark: yes'
                                ],
                                'value' => 'true'
				            ],
                            'options' => [
                                [
                                    "text" => [
                                        'type' => 'plain_text',
                                        'text' => ':heavy_check_mark: yes',
                                        'emoji' => true
						            ],
						            'value' => 'true'
                                ],
                                [
                                    "text" => [
                                        'type' => 'plain_text',
                                        'text' => ':x: no',
                                        'emoji' => true
                                    ],
                                    'value' => 'false'
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function getDailySummaryConfirmView(ModalSubmissionDto $modal): array
    {
        return [
            'response_action' => 'update',
            'view' => [
                'type' => 'modal',
                'title' => [
                    'type' => 'plain_text',
                    'text' => $modal->getTitle()
                ],
                'close' => [
                    'type' => 'plain_text',
                    'text' => 'Close'
                ],
                'blocks' => [
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => $modal->getMessage()
                        ],
                    ]
                ],
            ]
        ];
    }

    public function handleModalSubmission($evt, User $user): ModalSubmissionDto
    {
        $summary = $evt['view']['state']['values']['daily_summary_block']['summary_block_input']['value'];
        $doSendMail = $evt['view']['state']['values']['mail_block']['mail_choice']['selected_option']['value'] === 'true';

        try {
            $punchOutTimer = $this->punchTimerHandler->punchOut($user);
        } catch (MessageHandlerException $e) {
            return new ModalSubmissionDto(ModalSubmissionDto::STATUS_ERROR, ':heavy_exclamation_mark: '.$e->getMessage(), 'Something is wrong :(');
        }

        $timeOnWork = $this->time->getTimeSpentOnTypeByPeriod($user, 'day', TimerType::PUNCH);
        $timeOnBreak = $this->time->getTimeSpentOnTypeByPeriod($user, 'day', TimerType::BREAK);

        $ds = $this->updateOrCreateDailysummary($summary, $user, $timeOnWork, $timeOnBreak);

        $this->databaseHelper->flushAndPersist($ds);
        $doSendMail ? $this->mailer->sendDailySummaryMail($timeOnBreak, $timeOnWork-$timeOnBreak, $user, $ds->getDailySummary()) : null;

        $m = $this->getDailySummaryAddSlackMessage($timeOnWork, $timeOnBreak, $punchOutTimer, $doSendMail);
        return new ModalSubmissionDto(ModalSubmissionDto::STATUS_SUCCESS, $m->getBlockText(0), 'Success');
    }

    public function updateOrCreateDailysummary(string $summaryText, User $user, int $timeOnWork, int $timeOnBreak)
    {
        $dailySummaryEntity = $this->dailySummaryRepo->findOneBy(['date' => new \DateTime('now')]);
        return $this->dailySummaryFactory->createDailySummaryObject($summaryText, $user, $dailySummaryEntity, $timeOnWork, $timeOnBreak);
    }

    private function getDailySummaryAddSlackMessage(int $timeOnWork, int $timeOnBreak, $punchOutTimer, $doSendMail): SlackMessage
    {
        $formattedTimeOnBreak = $this->time->formatSecondsAsHoursAndMinutes($timeOnBreak);
        $formattedTimeOnWork = $this->time->formatSecondsAsHoursAndMinutes($timeOnWork - $timeOnBreak);

        $m = $this->slackMessageHelper->createSlackMessage();
        if ($punchOutTimer) {
            $breakText = $formattedTimeOnBreak === '0h 0min' ? '' : sprintf(' and *%s* on break', $formattedTimeOnBreak);
            $msg = sprintf(':heavy_check_mark: Signed you out for the day%s :call_me_hand:. You spent *%s* on work%s.', $doSendMail ? ' and sent your summary via mail' : '', $formattedTimeOnWork, $breakText);
            $this->slackMessageHelper->addTextSection($msg, $m);
            return $m;
        }

        $msg = $doSendMail ? 'Summary sent :slightly_smiling_face:' : 'Summary saved :slightly_smiling_face:';
        $this->slackMessageHelper->addTextSection($msg, $m);
        return $m;
    }
}
