<?php


namespace App\Handler\MessageHandler\Slack;

use App\Entity\Slack\SlackMessage;
use App\Entity\TimerType;
use App\Exceptions\MessageHandlerException;
use App\Services\DatabaseHelper;
use App\Services\Time;
use App\Services\UserProvider;
use App\Slack\SlackClient;
use Symfony\Component\HttpFoundation\Response;

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

        if ($evt['type'] !== 'app_mention') {
            throw new MessageHandlerException('Sorry, this is currently not supported.', 400);
        }

        try {
            $user = $this->userProvider->getDbUserBySlackId($slackUserId);
        } catch (\Exception $e) {
            throw new MessageHandlerException('Seems like you have not registered an account with YoTime yet. Please contact the admin of your slack workspace to add you to the service.', Response::HTTP_PRECONDITION_FAILED,);
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
                    $m->addTextSection('You have already punched out for today. :slightly_smiling_face:');
                    break;
                }

                $m->addTextSection('Signed you out for today. :call_me_hand:');

                extract($this->time->getTimesSpentByTypeAndPeriod($user, 'day'), EXTR_OVERWRITE);

                $formattedTimeOnWork = $this->time->formatSecondsAsHoursAndMinutes($work);
                $formattedTimeOnBreak = $this->time->formatSecondsAsHoursAndMinutes($break);
                $m->addTextSection(sprintf('You spent `%s` on work and `%s` on break.', $formattedTimeOnWork, $formattedTimeOnBreak));
                $this->databaseHelper->flushAndPersist($punchTimerStatusDto->getTimer());
                break;
            default:
                throw new MessageHandlerException(sprintf('You pinged me, however I do not recognise your command. Try `%s` to get a list of available commands', SlashCommandHandler::HELP), 400);
        }

        return $m;
    }
}
