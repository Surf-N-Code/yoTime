<?php


namespace App\Handler\MessageHandler\Slack;

use App\Entity\Slack\SlackMessage;
use App\Entity\TimerType;use App\Entity\User;use App\Exceptions\MessageHandlerException;
use App\Services\Time;
use App\Services\UserProvider;
use App\Slack\SlackClient;
use App\Slack\SlackMessageHelper;

class InteractionEventHandler
{
    private UserProvider $userProvider;

    private PunchTimerHandler $punchTimerHandler;

    private SlackMessageHelper $slackMessageHelper;

    private DailySummaryHandler $dailySummaryHandler;

    private SlackClient $slackClient;

    public function __construct(
        UserProvider $userProvider,
        PunchTimerHandler $punchTimerHandler,
        SlackMessageHelper $slackMessageHelper,
        DailySummaryHandler $dailySummaryHandler,
        SlackClient $slackClient
    )
    {
        $this->userProvider = $userProvider;
        $this->punchTimerHandler = $punchTimerHandler;
        $this->slackMessageHelper = $slackMessageHelper;
        $this->dailySummaryHandler = $dailySummaryHandler;
        $this->slackClient = $slackClient;
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

        if ($eventType === 'block_actions') {
//            $view = $this->dailySummaryHandler->getDailySummarySubmitView($evt['trigger_id']);
//            dump($view);
//            $view['view']['id'] = $evt['view']['id'];
//            $view['view']['private_metadata'] = $evt['actions'][0]['selected_options'][0]['value'];
//            dump($view);
//            $this->slackClient->slackApiCall('POST', 'views.update', $view);
            return (new SlackMessage())->addTextSection('Success');
        }
    }
}
