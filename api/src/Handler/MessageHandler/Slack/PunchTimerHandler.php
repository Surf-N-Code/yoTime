<?php


namespace App\Handler\MessageHandler\Slack;


use App\Entity\Slack\PunchTimerStatusDto;
use App\Entity\Slack\SlackMessage;
use App\Entity\Timer;use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Repository\TimerRepository;
use App\Services\DatabaseHelper;use App\Services\DateTimeProvider;
use App\Services\Time;

class PunchTimerHandler
{

    private TimerRepository $timeEntryRepo;
    private Time $time;
    private DatabaseHelper $databaseHelper;

    public function __construct(
        Time $time,
        TimerRepository $timeEntryRepo,
        DatabaseHelper $databaseHelper
    )
    {
        $this->timeEntryRepo = $timeEntryRepo;
        $this->time = $time;
        $this->databaseHelper = $databaseHelper;
    }

    public function punchIn(User $user): void
    {
        $this->throwOnExistingTimerFromToday($user);
        $timer = $this->time->startTimer($user, TimerType::WORK);
        $this->databaseHelper->flushAndPersist($timer);
    }

    public function punchOut(User $user): PunchTimerStatusDto
    {
        $timers = $this->timeEntryRepo->findTimersFromToday($user);

        if (empty($timers)) {
            throw new MessageHandlerException('Seems like you didn\'t sign in this morning. You can travel back in time to check yourself in for today by using the `/late_hi` command.', 412);
        }

        $latestTimer = $timers[count($timers)-1];
        if ($latestTimer->getDateEnd()) {
            return new PunchTimerStatusDto(false, $latestTimer);
        }

        $timer = $this->time->stopTimer($user, $latestTimer);
        $this->databaseHelper->flushAndPersist($timer);
        return new PunchTimerStatusDto(true, $latestTimer);
    }


    private function throwOnExistingTimerFromToday(User $user): void
    {
        $timers = $this->timeEntryRepo->findTimersFromToday($user);

        if (!empty($timers)) {
            throw new MessageHandlerException(sprintf('Seems like you have already signed in for today. The timer was started on `%s`.',
                $timers[0]->getDateStart()->format('d.m.Y H:i:s')
            ), 412);
        }
    }
}
