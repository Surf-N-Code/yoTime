<?php


namespace App\Handler\MessageHandler\Slack;


use App\Entity\Slack\SlashCommand;
use App\Entity\TimeEntry;use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Repository\TimeEntryRepository;
use App\Services\Time;

class TimerHandler
{

    private TimeEntryRepository $timeEntryRepo;

    private Time $time;

    private PunchTimerHandler $punchTimerHandler;

    public function __construct(
        Time $time,
        TimeEntryRepository $timeEntryRepo,
        PunchTimerHandler $punchTimerHandler
    )
    {
        $this->timeEntryRepo = $timeEntryRepo;
        $this->time = $time;
        $this->punchTimerHandler = $punchTimerHandler;
    }

    public function startTimer(string $commandStr, User $user): TimeEntry
    {
        $this->time->stopNonPunchTimers($user);
        $timerType = str_replace('/', '', $commandStr);
        return $this->time->startTimer($user, $timerType);
    }

    public function stopTimer(User $user, string $taskDescription = null): TimeEntry
    {
        $timeEntry = $this->timeEntryRepo->findNonPunchTimers($user);
        $this->throwWhenMissingTimer($timeEntry);

        if ($taskDescription) {
            $timeEntry = $this->time->addTaskToTimeEntry($timeEntry, $taskDescription);
        }

        return $this->time->stopTimer($user, $timeEntry);
    }

    public function addBreakManually(User $user, string $breakTimeStr): TimeEntry {
        $timeParts = $this->time->getHoursAndMinutesFromString($breakTimeStr);
        return $this->time->addFinishedTimer($user, TimerType::BREAK, $timeParts);
    }

    public function lateSignIn(User $user, string $timeStr): TimeEntry
    {
        $runningTimer = $this->timeEntryRepo->findPunchTimer($user);
        $this->throwWhenAlreadyPunchedIn($runningTimer);
        return $this->punchTimerHandler->punchInAtTime($user, $timeStr);
    }

    private function throwWhenMissingTimer(?TimeEntry $timeEntry)
    {
        if (!$timeEntry) {
            throw new MessageHandlerException(
                sprintf(
                    'No timer is running at the moment. Please start one using `/%s` or `/%s`',
                    TimerType::WORK,
                    TimerType::BREAK
                )
            );
        }
    }

    private function throwWhenAlreadyPunchedIn(?TimeEntry $runningTimer)
    {
        if ($runningTimer) {
            throw new MessageHandlerException(
                sprintf(
                    'You are already checked in for today since: `%s` :slightly_smiling_face:',
                    $runningTimer->getDateStart()->format('Y-m-d H:i:s')
                )
            );
        }
    }
}
