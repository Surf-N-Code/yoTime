<?php


namespace App\Handler\MessageHandler\Slack;


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

    public function punchIn(User $user): Timer
    {
        $this->getRunningPunchTimerOrThrow($user);
        $this->time->stopNonPunchTimers($user);
        return $this->time->startTimer($user, TimerType::PUNCH);
    }

    public function punchOut(User $user): bool
    {
        $signInOutTimer = $this->timeEntryRepo->findPunchTimer($user);

        if (!$signInOutTimer) {
            throw new MessageHandlerException('Seems like you didn\'t sign in this morning. You can travel back in time to check yourself in for today by using the `/late_hi` command.', 412);
        }

        if ($signInOutTimer->getDateEnd()) {
            return false;
        }

        $timeEntry = $this->time->stopTimer($user, $signInOutTimer);
        $this->databaseHelper->flushAndPersist($timeEntry);
        return true;
    }

    public function punchInAtTime(User $user, string $timeStr): Timer
    {
        $this->getRunningPunchTimerOrThrow($user);
        $this->time->stopNonPunchTimers($user);
        return $this->time->startTimerFromTimeString($user, $timeStr, TimerType::PUNCH);
    }

    private function getRunningPunchTimerOrThrow(User $user)
    {
        $signInOutTimer = $this->timeEntryRepo->findPunchTimer($user);

        if ($signInOutTimer) {
            throw new MessageHandlerException(sprintf('Seems like you have already signed in for today. The timer was started on `%s`.',
                $signInOutTimer->getDateStart()->format('d.m.Y H:i:s')
            ));
        }
        return $signInOutTimer;
    }
}
