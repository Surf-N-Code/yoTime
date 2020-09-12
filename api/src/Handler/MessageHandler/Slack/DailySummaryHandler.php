<?php


namespace App\Handler\MessageHandler\Slack;


use App\Entity\Slack\SlackMessage;
use App\Entity\Slack\SlashCommand;
use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Mail\Mailer;
use App\ObjectFactories\DailySummaryFactory;use App\Repository\DailySummaryRepository;
use App\Services\DatabaseHelper;
use App\Services\Time;
use App\Slack\SlackClient;
use Symfony\Component\Mailer\MailerInterface;

class DailySummaryHandler
{

    private DailySummaryRepository $dailySummaryRepo;

    private Time $time;

    private PunchTimerHandler $punchTimerHandler;

    private DailySummaryFactory $dailySummaryFactory;

    private SlackClient $slackClient;

    private DatabaseHelper $databaseHelper;

    private Mailer $mailer;

    public function __construct(
        PunchTimerHandler $punchTimerHandler,
        DailySummaryRepository $dailySummaryRepo,
        Time $time,
        DailySummaryFactory $dailySummaryFactory,
        SlackClient $slackClient,
        DatabaseHelper $databaseHelper,
        Mailer $mailer
    )
    {
        $this->dailySummaryRepo = $dailySummaryRepo;
        $this->time = $time;
        $this->punchTimerHandler = $punchTimerHandler;
        $this->dailySummaryFactory = $dailySummaryFactory;
        $this->slackClient = $slackClient;
        $this->databaseHelper = $databaseHelper;
        $this->mailer = $mailer;
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
                        'block_id' => 'ml_block',
                        'element' => [
                            'type' => 'plain_text_input',
                            'action_id' => 'ml_input',
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
                        'label' => [
                            'type' => 'plain_text',
                            'text' => 'Send E-Mail?',
                            'emoji' => true
                        ],
                        'element' => [
                            'type' => 'static_select',
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
                                'value' => 'value-0'
				            ],
                            'options' => [
                                [
                                    "text" => [
                                        'type' => 'plain_text',
                                        'text' => ':heavy_check_mark: yes',
                                        'emoji' => true
						            ],
						            'value' => 'value-0'
                                ],
                                [
                                    "text" => [
                                        'type' => 'plain_text',
                                        'text' => ':x: no',
                                        'emoji' => true
                                    ],
                                    'value' => 'value-1'
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function getDailySummaryConfirmView(SlackMessage $m): array
    {
        return [
            'response_action' => 'update',
            'view' => [
                'type' => 'modal',
                'title' => [
                    'type' => 'plain_text',
                    'text' => 'Daily Summary sent!'
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
                            'text' => $m->getBlockText(0)
                        ],
                    ]
                ],
            ]
        ];
    }

    public function handleDailySummaryEvent(string $summary, User $user)
    {

        try {
            $punchOutTimer = $this->punchTimerHandler->punchOut($user);
        } catch (MessageHandlerException $e) {
            return (new SlackMessage())->addTextSection($e->getMessage());
        }

        $timeOnWork = $this->time->getTimeSpentOnTypeByPeriod($user, 'day', TimerType::PUNCH);
        $timeOnBreak = $this->time->getTimeSpentOnTypeByPeriod($user, 'day', TimerType::BREAK);

        $ds = $this->updateOrCreateDailysummary($summary, $user, $timeOnWork, $timeOnBreak);

        $this->databaseHelper->flushAndPersist($ds);
        $this->mailer->sendDAilySummaryMail(($timeOnWork-$timeOnBreak), $timeOnBreak, $user, $ds->getDailySummary());

        return $this->getDailySummaryAddSlackMessage($timeOnWork, $timeOnBreak, $punchOutTimer);
    }

    public function updateOrCreateDailysummary(string $summary, User $user, int $timeOnWork, int $timeOnBreak)
    {
        $dailySummary = $this->dailySummaryRepo->findOneBy(['date' => new \DateTime('now')]);
        return $this->dailySummaryFactory->createDailySummaryObject($summary, $user, $dailySummary, $timeOnWork, $timeOnBreak);
    }

    private function getDailySummaryAddSlackMessage(int $timeOnWork, int $timeOnBreak, $punchOutTimer): SlackMessage
    {
        $m = new SlackMessage();

        $formattedTimeOnBreak = $this->time->formatSecondsAsHoursAndMinutes($timeOnBreak);
        $formattedTimeOnWork = $this->time->formatSecondsAsHoursAndMinutes($timeOnWork - $timeOnBreak);

        if ($punchOutTimer) {
            $msg = sprintf(':heavy_check_mark: Signed you out for the day and sent your daily summary :call_me_hand:. \n\nYou spent *%s* on work and *%s* on break.', $formattedTimeOnWork, $formattedTimeOnBreak ?? '0h 0m');
        }
        return $m->addTextSection($msg ?? 'Sent your daily summary for today.');
    }
}
