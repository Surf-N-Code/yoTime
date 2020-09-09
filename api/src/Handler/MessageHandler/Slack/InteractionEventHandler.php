<?php


namespace App\Handler\MessageHandler\Slack;

use App\Entity\Slack\SlackMessage;
use App\Entity\TimerType;use App\Entity\User;use App\Exceptions\MessageHandlerException;
use App\Services\Time;
use App\Services\UserProvider;use App\Slack\SlackMessageHelper;

class InteractionEventHandler
{
    private UserProvider $userProvider;

    private PunchTimerHandler $punchTimerHandler;

    private SlackMessageHelper $slackMessageHelper;

    private DailySummaryHandler $dailySummaryHandler;

    public function __construct(
        UserProvider $userProvider,
        PunchTimerHandler $punchTimerHandler,
        SlackMessageHelper $slackMessageHelper,
        DailySummaryHandler $dailySummaryHandler
    )
    {
        $this->userProvider = $userProvider;
        $this->punchTimerHandler = $punchTimerHandler;
        $this->slackMessageHelper = $slackMessageHelper;
        $this->dailySummaryHandler = $dailySummaryHandler;
    }

    public function parseEventType($evt)
    {
        $slackUserId = $evt['user']['id'];
        $eventType = $evt['type'];

        try {
            $user = $this->userProvider->getDbUserBySlackId($slackUserId);
        } catch (\Exception $e) {
            throw new MessageHandlerException('Seems like you have not registered an account with YoTime yet. Please contact the admin of your slack workspace to add you to the service.', 412,);
        }

        if ($eventType === 'view_submission') {
            $summaryText = $evt['view']['state']['values']['ml_block']['ml_input']['value'];
            return $this->dailySummaryHandler->handleDailySummaryEvent($summaryText, $user);
        }
    }
}
