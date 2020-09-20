<?php


namespace App\Handler\MessageHandler\Slack;


use App\Entity\Slack\ModalSubmissionDto;
use App\Entity\Slack\SlackMessage;
use App\Entity\Timer;
use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\HrTools\Personio\Gateway;
use App\Mail\Mailer;
use App\ObjectFactories\DailySummaryFactory;use App\Repository\DailySummaryRepository;
use App\Services\DatabaseHelper;
use App\Services\Time;
use App\Slack\SlackMessageHelper;
use Psr\Log\LoggerInterface;

class DailySummaryHandler
{

    private DailySummaryRepository $dailySummaryRepo;

    private Time $time;

    private PunchTimerHandler $punchTimerHandler;

    private DailySummaryFactory $dailySummaryFactory;

    private DatabaseHelper $databaseHelper;

    private Mailer $mailer;

    private SlackMessageHelper $slackMessageHelper;

    private Gateway $personio;

    private LoggerInterface $logger;

    public function __construct(
        PunchTimerHandler $punchTimerHandler,
        DailySummaryRepository $dailySummaryRepo,
        Time $time,
        DailySummaryFactory $dailySummaryFactory,
        DatabaseHelper $databaseHelper,
        Mailer $mailer,
        SlackMessageHelper $slackMessageHelper,
        Gateway $personio,
        LoggerInterface $logger
    )
    {
        $this->dailySummaryRepo = $dailySummaryRepo;
        $this->time = $time;
        $this->punchTimerHandler = $punchTimerHandler;
        $this->dailySummaryFactory = $dailySummaryFactory;
        $this->databaseHelper = $databaseHelper;
        $this->mailer = $mailer;
        $this->slackMessageHelper = $slackMessageHelper;
        $this->personio = $personio;
        $this->logger = $logger;
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
            $punchTimerStatus = $this->punchTimerHandler->punchOut($user);
        } catch (MessageHandlerException $e) {
            return new ModalSubmissionDto(ModalSubmissionDto::STATUS_ERROR, ':heavy_exclamation_mark: '.$e->getMessage(), 'Something is wrong :(');
        }

        $timeOnWork = $this->time->getTimeSpentOnTypeByPeriod($user, 'day', TimerType::PUNCH);
        $timeOnBreak = $this->time->getTimeSpentOnTypeByPeriod($user, 'day', TimerType::BREAK);

        $dailySummaryEntity = $this->dailySummaryRepo->findOneBy(['date' => new \DateTime('now')]);
        $ds = $this->dailySummaryFactory->createDailySummaryObject($summary, $user, $punchTimerStatus->getTimer(), $dailySummaryEntity, $timeOnWork, $timeOnBreak);

        if ($doSendMail) {
            try {
                $this->mailer->sendDailySummaryMail($timeOnBreak, $timeOnWork - $timeOnBreak, $user, $ds->getDailySummary());
                $ds->setIsEmailSent(true);
            } catch (MessageHandlerException $e) {
                $ds->setIsEmailSent(false);
            }
        }

        if (!$ds->getIsSyncedToPersonio()) {
            try {
                $response = $this->personio->postAttendanceForEmployee(2269559, $ds);
                $didSyncToPersonio = $response['success'];
                if (!$didSyncToPersonio) {

                    $this->logger->error(sprintf('Could not sync attendances to Persoio with message: %s', $response['error']['message']));
                }
            } catch (\Exception $e) {
                $didSyncToPersonio = false;
            }
            $ds->setIsSyncedToPersonio($didSyncToPersonio);
        }

        $m = $this->getDailySummaryAddSlackMessage($timeOnWork, $timeOnBreak, $punchTimerStatus->getActionStatus(), $doSendMail, $ds->getIsSyncedToPersonio(), $didSyncToPersonio ?? false);

        $this->databaseHelper->flushAndPersist($ds);

        return new ModalSubmissionDto(ModalSubmissionDto::STATUS_SUCCESS, $m->getBlockText(0), 'Jabadabadingboombang!');
    }

    private function getDailySummaryAddSlackMessage(int $timeOnWork, int $timeOnBreak, bool $didPunchOut, bool $doSendMail, bool $alreadySynchedToPersonio, bool $didSyncToPersonio): SlackMessage
    {
        $formattedTimeOnBreak = $this->time->formatSecondsAsHoursAndMinutes($timeOnBreak);
        $formattedTimeOnWork = $this->time->formatSecondsAsHoursAndMinutes($timeOnWork - $timeOnBreak);

        $m = $this->slackMessageHelper->createSlackMessage();
        if ($didPunchOut) {
            $breakText = $formattedTimeOnBreak === '0h 0min' ? '' : sprintf(' and *%s* on break', $formattedTimeOnBreak);
            $msg = sprintf(':heavy_check_mark: Signed you out for the day%s :call_me_hand:. You spent *%s* on work%s.', $doSendMail ? ' and sent your summary via mail' : '', $formattedTimeOnWork, $breakText);
            $msg .= $this->getPersonioSyncStatusMessage($didSyncToPersonio, $alreadySynchedToPersonio);
            $this->slackMessageHelper->addTextSection($msg, $m);
            return $m;
        }

        $msg = $doSendMail ? 'Summary sent :slightly_smiling_face:' : 'Summary saved :slightly_smiling_face:';
        $msg .= $this->getPersonioSyncStatusMessage($didSyncToPersonio, $alreadySynchedToPersonio);
        $this->slackMessageHelper->addTextSection($msg, $m);
        return $m;
    }

    private function getPersonioSyncStatusMessage(bool $didSyncToPersonio, bool $alreadySyncedToPersonio)
    {
        if (!$alreadySyncedToPersonio) {
            if (!$didSyncToPersonio) {
                return PHP_EOL . PHP_EOL . ':x: Error syncing your attendance to Personio.';
            }
            return PHP_EOL . PHP_EOL . ':heavy_check_mark: Synced your attendance to Personio.';
        }

        return '';
    }
}
