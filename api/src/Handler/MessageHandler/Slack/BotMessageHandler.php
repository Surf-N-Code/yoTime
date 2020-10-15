<?php


namespace App\Handler\MessageHandler\Slack;

use App\Entity\Slack\SlackMessage;
use App\Entity\TimerType;use App\Entity\User;use App\Exceptions\MessageHandlerException;
use App\Services\DatabaseHelper;
use App\Services\Time;
use App\Services\UserProvider;
use App\Slack\SlackClient;
use App\Slack\SlackMessageHelper;

class BotMessageHandler
{
    private UserProvider $userProvider;

    private TimerHandler $timerHandler;

    private Time $time;

    private SlackClient $slackClient;

    private DatabaseHelper $databaseHelper;

    public function __construct(
        UserProvider $userProvider,
        TimerHandler $timerHandler,
        Time $time,
        SlackClient $slackClient,
        DatabaseHelper $databaseHelper
    )
    {
        $this->userProvider = $userProvider;
        $this->timerHandler = $timerHandler;
        $this->time = $time;
        $this->slackClient = $slackClient;
        $this->databaseHelper = $databaseHelper;
    }

    public function parseEventType($evt): SlackMessage
    {
        $command = strtolower($evt['text']);
        $slackUserId = $evt['user'];
        $channel = $evt['channel'];

        if ($evt['type'] !== 'app_mention') {
            throw new MessageHandlerException('Sorry, this is currently not supported.', 400);
        }

        try {
            $user = $this->userProvider->getDbUserBySlackId($slackUserId);
        } catch (\Exception $e) {
            throw new MessageHandlerException('Seems like you have not registered an account with YoTime yet. Please contact the admin of your slack workspace to add you to the service.', 412,);
        }

        $m = new SlackMessage();
        switch ($command) {
            case strpos($command, 'hey') !== false:
                $timer = $this->timerHandler->startTimer($user, TimerType::WORK);
                $m->addTextSection('Happy working :rocket:');
                $this->databaseHelper->flushAndPersist($timer);
                break;

            case strpos($command, 'bye') !== false:
               $punchTimerStatusDto = $this->timerHandler->punchOut($user);
                if (!$punchTimerStatusDto->didSignOut()) {
                    $m->addTextSection('You have already punched out for today.');
                    break;
                }


                $m->addTextSection('Signed you out for today. :call_me_hand:');

                $timeOnWork = $this->time->getTimeSpentOnTypeByPeriod($user, 'day', TimerType::WORK);
                $timeOnBreak = $this->time->getTimeSpentOnTypeByPeriod($user, 'day', TimerType::BREAK);

                $formattedTimeOnWork = $this->time->formatSecondsAsHoursAndMinutes($timeOnWork - $timeOnBreak);
                $formattedTimeOnBreak = $this->time->formatSecondsAsHoursAndMinutes($timeOnBreak);
                $m->addTextSection(sprintf('You spent `%s` on work and `%s` on break.', $formattedTimeOnWork, $formattedTimeOnBreak));
                $this->databaseHelper->flushAndPersist($punchTimerStatusDto->getTimer());
                break;
            default:
                throw new MessageHandlerException('You pinged me, however I do not recognise your command. Try `/help` to get a list of available commands', 400);
        }

        $this->sendEphemeral($channel, $slackUserId, $m);

        return $m;
    }

    private function sendEphemeral(string $channel, string $userId, SlackMessage $m)
    {
        $this->slackClient->slackApiCall('POST', 'chat.postEphemeral', [
            'channel' => $channel,
            'user' => $userId,
            'text' => $m->getBlockText(0),
            'blocks' => $m->getBlocks()
        ]);
    }
}
