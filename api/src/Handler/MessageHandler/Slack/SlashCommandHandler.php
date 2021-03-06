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

    const STOP_TIMER = '/stop-timer';

    const START_WORK = '/work';

    const START_BREAK = '/break';

    const LATE_HI = '/late-hi';

    const REGISTER = '/register';

    const LATE_BREAK = '/late-break';

    const REPORT = '/report';

    const DAILY_SUMMARY = '/ds';

    const HELP = '/help-me';

    private UserProvider $userProvider;

    private TimerHandler $timerHandler;

    private UserHelpHandler $userHelpHandler;

    private DailySummaryHandler $dailySummaryHandler;

    private DatabaseHelper $databaseHelper;

    private Time $time;

    private SlackClient $slackClient;

    private RegisterHandler $registerHandler;

    private ReportingHandler $reportingHandler;

    public function __construct(
        UserHelpHandler $userHelpHandler,
        DailySummaryHandler $dailySummaryHandler,
        TimerHandler $timerHandler,
        UserProvider $userProvider,
        DatabaseHelper $databaseHelper,
        Time $time,
        SlackClient $slackClient,
        RegisterHandler $registerHandler,
        ReportingHandler $reportingHandler
    )
    {
        $this->userHelpHandler = $userHelpHandler;
        $this->dailySummaryHandler = $dailySummaryHandler;
        $this->timerHandler = $timerHandler;
        $this->userProvider = $userProvider;
        $this->databaseHelper = $databaseHelper;
        $this->time = $time;
        $this->slackClient = $slackClient;
        $this->registerHandler = $registerHandler;
        $this->reportingHandler = $reportingHandler;
    }

    public function getSlashCommandToExecute(SlashCommand $command): int
    {
        $message = new SlackMessage();
        $user = $this->getUser($command->getUserId());
        $commandStr = $command->getCommand();
        $commandText = $command->getText();
        $responseUrl = $command->getResponseUrl();
        switch ($commandStr) {
            case '/'.self::START_WORK:
            case '/'.self::START_BREAK:
                $timer = $this->timerHandler->startTimer($user, $commandStr);
                $message = $message->addTextSection(sprintf(':clock9: %s timer started', ucfirst($timer->getTimerType())));
                $this->databaseHelper->flushAndPersist($timer);
                $this->sendSlackMessage($responseUrl, $message);
                return Response::HTTP_CREATED;

            case self::LATE_HI:
                $timer = $this->timerHandler->lateSignIn($user, $commandText);
                $message->addTextSection(sprintf('Checked you in at `%s` :rocket:', $timer->getDateStart()->format('d.m.Y H:i:s')));
                $this->databaseHelper->flushAndPersist($timer);
                $this->sendSlackMessage($responseUrl, $message);
                return Response::HTTP_CREATED;

            case self::LATE_BREAK:
                $timer = $this->time->addFinishedTimer($user, TimerType::BREAK, $commandText);
                $message->addTextSection(':sleeping: Break added');
                $this->databaseHelper->flushAndPersist($timer);
                $this->sendSlackMessage($responseUrl, $message);
                return Response::HTTP_CREATED;

            case self::STOP_TIMER:
                $timer = $this->timerHandler->stopTimer($user, $commandText);
                $timeSpent = $this->time->formatSecondsAsHoursAndMinutes(
                    abs($timer->getDateEnd()->getTimestamp() - $timer->getDateStart()->getTimestamp())
                );
                $msg = sprintf('You spent `%s` on `%s`', $timeSpent, $timer->getTimerType());

                $message->addTextSection(sprintf('Timer stopped. %s', $msg));
                $this->databaseHelper->flushAndPersist($timer);
                $this->sendSlackMessage($responseUrl, $message);
                return Response::HTTP_OK;

            case self::DAILY_SUMMARY:
                $modal = $this->dailySummaryHandler->getDailySummarySubmitView($command->getTriggerId());
                $this->slackClient->slackApiCall('POST', 'views.open', $modal);
                return Response::HTTP_CREATED;

            case self::HELP:
                $message = $this->userHelpHandler->showUserHelp($command);
                $this->sendSlackMessage($responseUrl, $message);
                return Response::HTTP_OK;

            case self::REGISTER:
                $message = $this->registerHandler->register($command);
                $this->sendSlackMessage($responseUrl, $message);
                return Response::HTTP_CREATED;

            case self::REPORT:
                $message = $this->reportingHandler->getUserReport($user, $command);
                $this->sendSlackMessage($responseUrl, $message);
                return Response::HTTP_OK;

            default:
                $message->addTextSection(sprintf('Command `%s` is not supported. Try `/help_me` for a list of available commands',$command->getCommand()));
                return Response::HTTP_OK;
        }
    }

    private function getUser(string $slackUserId): User
    {
        $message = 'Seems like you have not registered an account with YoTime yet. Please contact your Slack admin to be added to the service';

        try {
            $user = $this->userProvider->getDbUserBySlackId($slackUserId);
        } catch (NotFoundHttpException $e) {
            throw new SlashCommandException($message, 400);
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
