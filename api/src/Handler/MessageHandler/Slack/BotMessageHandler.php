<?php


namespace App\Handler\MessageHandler\Slack;

use App\Entity\Slack\SlackMessage;
use App\Entity\TimerType;use App\Entity\User;use App\Exceptions\MessageHandlerException;
use App\Services\Time;
use App\Services\UserProvider;use App\Slack\SlackMessageHelper;

class BotMessageHandler
{
    private UserProvider $userProvider;
    private PunchTimerHandler $punchTimerHandler;
    private SlackMessageHelper $slackMessageHelper;

    public function __construct(
        UserProvider $userProvider,
        PunchTimerHandler $punchTimerHandler,
        SlackMessageHelper $slackMessageHelper,
        Time $time
    )
    {
        $this->userProvider = $userProvider;
        $this->punchTimerHandler = $punchTimerHandler;
        $this->slackMessageHelper = $slackMessageHelper;
    }

    public function parseEventType($evt): SlackMessage
    {
        $command = strtolower($evt['text']);
        $slackUserId = $evt['user'];

        if ($evt['type'] !== 'app_mention') {
            throw new MessageHandlerException('Sorry, this is currently not supported.', 400);
        }

        try {
            $user = $this->userProvider->getDbUserBySlackId($slackUserId);
        } catch (\Exception $e) {
            throw new MessageHandlerException('Seems like you have not registered an account with YoTime yet. Please contact the admin of your slack workspace to add you to the service.', 412,);
        }

        $m = $this->slackMessageHelper->createSlackMessage();
        switch ($command) {
            case strpos($command, '/hi') !== false:
                $this->punchTimerHandler->punchIn($user);
                $m->addTextSection('Happy working :rocket:');
                break;

            case strpos($command, '/bye') !== false:
                $this->punchTimerHandler->punchOut($user);
                $m->addTextSection('You ' . $this->slackMessageHelper->getFormattedTimeSpentOnWorkAndBreak($user));
                break;
            default:
                throw new MessageHandlerException('You pinged me, however I do not recognise your command. Try `/help` to get a list of available commands', 412);
        }

        return $m;
    }
}
