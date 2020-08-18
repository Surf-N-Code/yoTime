<?php


namespace App\Handler\MessageHandler\Slack;


use App\Entity\Slack\SlackMessage;
use App\Entity\Slack\SlashCommand;
use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\ObjectFactories\DailySummaryFactory;use App\Repository\DailySummaryRepository;
use App\Services\Time;

class DailySummaryHandler
{

    private DailySummaryRepository $dailySummaryRepo;

    private Time $time;

    private PunchTimerHandler $punchTimerHandler;

    private DailySummaryFactory $dailySummaryFactory;

    public function __construct(
        PunchTimerHandler $punchTimerHandler,
        DailySummaryRepository $dailySummaryRepo,
        Time $time,
        DailySummaryFactory $dailySummaryFactory
    )
    {
        $this->dailySummaryRepo = $dailySummaryRepo;
        $this->time = $time;
        $this->punchTimerHandler = $punchTimerHandler;
        $this->dailySummaryFactory = $dailySummaryFactory;
    }

    public function addDailySummaryFromSlackCommand(string $summary, User $user)
    {
        if ($summary === '' || !$summary) {
            throw new MessageHandlerException('The daily summary is empty. Please provide some content for your daily summary. You could for example list the tasks you completed today. :call_me_hand:');
        }

        $punchOutTimer = $this->punchTimerHandler->punchOut($user);

        $timeOnWork = $this->time->getTimeSpentOnTypeByPeriod($user, 'day', TimerType::WORK);
        $timeOnBreak = $this->time->getTimeSpentOnTypeByPeriod($user, 'day', TimerType::BREAK);

        $ds = $this->updateOrCreateDailysummary($summary, $user, $timeOnWork, $timeOnBreak);

        return [
            $this->getDailySummaryAddSlackMessage($timeOnWork, $timeOnBreak, $punchOutTimer),
            $ds
        ];
    }

    public function updateOrCreateDailysummary(string $summary, User $user, int $timeOnWork, int $timeOnBreak)
    {
        $dailySummary = $this->dailySummaryRepo->findOneBy(['date' => new \DateTime('now')]);
        return $this->dailySummaryFactory->createDailySummaryObject($summary, $dailySummary, $user, $timeOnWork, $timeOnBreak);
    }

    private function getDailySummaryAddSlackMessage(int $timeOnWork, int $timeOnBreak, $punchOutTimer): SlackMessage
    {
        $m = new SlackMessage();

        $formattedTimeOnBreak = $this->time->formatSecondsAsHoursAndMinutes($timeOnBreak);
        $formattedTimeOnWork = $this->time->formatSecondsAsHoursAndMinutes($timeOnWork - $timeOnBreak);

        if ($punchOutTimer) {
            $msg = sprintf('Signed you out for the day and sent your daily summary :call_me_hand:. You spent `%s` on work and `%s` on break.', $formattedTimeOnWork, $formattedTimeOnBreak ?? '0h 0m');
        }
        return $m->addTextSection($msg ?? 'Sent your daily summary for today.');
    }
}
