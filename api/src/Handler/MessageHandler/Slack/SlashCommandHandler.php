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
use App\Slack\SlackClient;
use App\Slack\SlackMessageHelper;
use Doctrine\DBAL\DBALException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SlashCommandHandler {

    private UserProvider $userProvider;

    private TimerHandler $timerHandler;

    private UserHelpHandler $userHelpHandler;

    private DailySummaryHandler $dailySummaryHandler;

    private SlackMessageHelper $slackMessageHelper;

    private DatabaseHelper $databaseHelper;

    private Time $time;

    private SlackClient $slackClient;

    public function __construct(
        UserHelpHandler $userHelpHandler,
        DailySummaryHandler $dailySummaryHandler,
        TimerHandler $timerHandler,
        UserProvider $userProvider,
        DatabaseHelper $databaseHelper,
        Time $time,
        SlackClient $slackClient
    )
    {
        $this->userHelpHandler = $userHelpHandler;
        $this->dailySummaryHandler = $dailySummaryHandler;
        $this->timerHandler = $timerHandler;
        $this->userProvider = $userProvider;
        $this->databaseHelper = $databaseHelper;
        $this->time = $time;
        $this->slackClient = $slackClient;
    }

    public function getSlashCommandToExecute(SlashCommand $command)
    {
        $message = new SlackMessage();
        $user = $this->getUser($command->getUserId());
        $commandStr = $command->getCommand();
        $commandText = $command->getText();
        $responseUrl = $command->getResponseUrl();
        switch ($commandStr) {
            case '/'.TimerType::WORK:
            case '/'.TimerType::BREAK:
                $timer = $this->timerHandler->startTimer($user, $commandStr);
                $message = $message->addTextSection(sprintf('%s timer started', ucfirst($timer->getTimerType())));
                $this->databaseHelper->flushAndPersist($timer);
                $this->sendSlackMessage($responseUrl, $message);
                break;

            case '/late_hi':
                $timer = $this->timerHandler->lateSignIn($user, $commandText);
                $message->addTextSection(sprintf('Checked you in at %s :rocket:', $timer->getDateStart()->format('d.m.Y H:i:s')));
                $this->databaseHelper->flushAndPersist($timer);
                $this->sendSlackMessage($responseUrl, $message);
                break;

            case '/late_break':
                $timer = $this->time->addFinishedTimer($user, TimerType::BREAK, $commandText);
                $message->addTextSection('Break successfully added');
                $this->databaseHelper->flushAndPersist($timer);
                $this->sendSlackMessage($responseUrl, $message);
                break;

            case '/end_break':
            case '/end_work':
                $timer = $this->timerHandler->stopTimer($user, $commandText);
                $timeSpent = $this->time->formatSecondsAsHoursAndMinutes(
                    abs($timer->getDateEnd()->getTimestamp() - $timer->getDateStart()->getTimestamp())
                );
                $msg = sprintf('You spent `%s` on `%s`', $timeSpent, $timer->getTimerType());

                $message->addTextSection(sprintf('Timer stopped. %s', $msg));
                $this->databaseHelper->flushAndPersist($timer);
                $this->sendSlackMessage($responseUrl, $message);
                break;

            case '/dailysummary':
            case '/ds':
                $modal = $this->dailySummaryHandler->getDailySummarySubmitView($command->getTriggerId());
                $this->slackClient->slackApiCall('POST', 'views.open', $modal);
                break;

            case '/help':
                $message = $this->userHelpHandler->showUserHelp($command);
                break;

            default:
                $message->addTextSection(sprintf('Command `%s` is not supported. Try `/help` for a list of available commands',$command->getCommand()));
        }
    }

    private function getUser(string $slackUserId): User
    {
        $message = 'Seems like you have not registered an account with YoTime yet. Please contact your Slack admin to be added to the service';

        try {
            $user = $this->userProvider->getDbUserBySlackId($slackUserId);
        } catch (NotFoundHttpException $e) {
            throw new SlashCommandException($message, 412);
        }

        return $user;
    }

    private function sendSlackMessage(string $respnseUrl, SlackMessage $m)
    {
        $this->slackClient->slackWebhook([
            'response_url' => $respnseUrl,
            'blocks' => $m->getBlocks()
        ]);
    }
}
