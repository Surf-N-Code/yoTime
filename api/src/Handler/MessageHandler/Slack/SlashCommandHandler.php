<?php


namespace App\Handler\MessageHandler\Slack;


use App\Entity\Slack\SlackMessage;
use App\Entity\Slack\SlashCommand;
use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\SlashCommandException;
use App\Mail\Mailer;use App\Services\DatabaseHelper;
use App\Services\Time;
use App\Services\UserProvider;
use App\Slack\SlackMessageHelper;

class SlashCommandHandler {

    private UserProvider $userProvider;
    private TimerHandler $timerHandler;
    private UserHelpHandler $userHelpHandler;
    private DailySummaryHandler $dailySummaryHandler;
    private SlackMessageHelper $slackMessageHelper;
    private DatabaseHelper $databaseHelper;
    private Time $time;
    private Mailer $mailer;

    public function __construct(
        UserHelpHandler $userHelpHandler,
        DailySummaryHandler $dailySummaryHandler,
        TimerHandler $timerHandler,
        UserProvider $userProvider,
        DatabaseHelper $databaseHelper,
        Mailer $mailer,
        Time $time
    )
    {
        $this->userHelpHandler = $userHelpHandler;
        $this->dailySummaryHandler = $dailySummaryHandler;
        $this->timerHandler = $timerHandler;
        $this->userProvider = $userProvider;
        $this->databaseHelper = $databaseHelper;
        $this->mailer = $mailer;
        $this->time = $time;
    }

    public function getSlashCommandToExecute(SlashCommand $command): ?SlackMessage
    {
        $message = new SlackMessage();
        $user = $this->getUser($command->getUserId());
        $commandStr = $command->getCommand();
        $commandText = $command->getText();
        switch ($commandStr) {
            case '/'.TimerType::WORK:
            case '/'.TimerType::BREAK:
                $objectToPersist = $this->timerHandler->startTimer($commandStr, $user);
                $message = $message->addTextSection(sprintf('%s timer started', ucfirst($objectToPersist->getTimerType())));
                break;

            case '/late_hi':
                $objectToPersist = $this->timerHandler->lateSignIn($user, $commandText);
                $message->addTextSection(sprintf('Checked you in at %s :rocket:', $objectToPersist->getDateStart()->format('d.m.Y H:i:s')));
                break;

            case '/late_break':
                $objectToPersist = $this->timerHandler->addBreakManually($user, $commandText);
                $message->addTextSection('Break successfully added');
                break;

            case '/end_break':
            case '/end_work':
                $objectToPersist = $this->timerHandler->stopTimer($user, $commandText);
                $timeSpent = $this->time->formatSecondsAsHoursAndMinutes(
                    abs($objectToPersist->getDateEnd()->getTimestamp() - $objectToPersist->getDateStart()->getTimestamp())
                );
                $msg = sprintf('You spent `%s` on `%s`', $timeSpent, $objectToPersist->getTimerType());

                $message->addTextSection(sprintf('Timer stopped. %s', $msg));
                break;

            case '/dailysummary':
            case '/ds':
                [$message, $objectToPersist] = $this->dailySummaryHandler->addDailySummaryFromSlackCommand($command->getText(), $user);
                $timeOnWorkInS = $objectToPersist->getTimeWorkedInS();
                $timeOnBreakInS = $objectToPersist->getTimeBreakInS();
                $this->mailer->sendDAilySummaryMail(($timeOnWorkInS-$timeOnBreakInS), $timeOnBreakInS, $user, $objectToPersist->getDailySummary());
                break;

            case '/help':
                $message = $this->userHelpHandler->showUserHelp($command);
                break;

            default:
                $message->addTextSection(sprintf('Command `%s` is not supported. Try `/help` for a list of available commands',$command->getCommand()));
        }

        if ($objectToPersist) {
            $this->databaseHelper->flushAndPersist($objectToPersist);
        }

        return $message;
    }

    private function getUser(string $slackUserId): User
    {
        $message = 'Seems like you have not registered an account with YoTime yet. Please contact your Slack admin to be added to the service';

        try {
            $user = $this->userProvider->getDbUserBySlackId($slackUserId);
        } catch (\Exception $e) {
            throw new SlashCommandException($message, 412);
        }

        return $user;
    }
}
