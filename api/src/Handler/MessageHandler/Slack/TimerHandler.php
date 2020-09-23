<?php


namespace App\Handler\MessageHandler\Slack;


use App\Entity\Slack\SlashCommand;
use App\Entity\Timer;use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Repository\TimerRepository;
use App\Services\Time;

class TimerHandler
{

    private TimerRepository $timeEntryRepo;

    private Time $time;

    private PunchTimerHandler $punchTimerHandler;

    public function __construct(
        Time $time,
        TimerRepository $timeEntryRepo,
        PunchTimerHandler $punchTimerHandler
    )
    {
        $this->timeEntryRepo = $timeEntryRepo;
        $this->time = $time;
        $this->punchTimerHandler = $punchTimerHandler;
    }

    public function startTimer(User $user, string $commandStr): Timer
    {
        $this->time->stopNonPunchTimers($user);
        $timerType = str_replace('/', '', $commandStr);
        return $this->time->startTimer($user, $timerType);
    }

    public function stopTimer(User $user, string $taskDescription = null): Timer
    {
        $timer = $this->timeEntryRepo->findNonPunchTimer($user);
        $this->throwWhenMissingTimer($timer);

        if ($taskDescription) {
            $timer = $this->time->addTaskToTimer($timer, $taskDescription);
        }

        return $this->time->stopTimer($user, $timer);
    }

    public function lateSignIn(User $user, string $timeStr): Timer
    {
        $runningTimer = $this->timeEntryRepo->findPunchTimer($user);
        $this->throwWhenAlreadyPunchedIn($runningTimer);
        return $this->punchTimerHandler->punchInAtTime($user, $timeStr);
    }

    private function throwWhenMissingTimer(?Timer $timeEntry)
    {
        if (!$timeEntry) {
            throw new MessageHandlerException(
                sprintf(
                    'No timer is running at the moment. Please start one using `/%s` or `/%s`',
                    TimerType::WORK,
                    TimerType::BREAK
                ),
                412
            );
        }
    }

    private function throwWhenAlreadyPunchedIn(?Timer $runningTimer)
    {
        if ($runningTimer) {
            throw new MessageHandlerException(
                sprintf(
                    'You are already checked in for today since: `%s` :slightly_smiling_face:',
                    $runningTimer->getDateStart()->format('Y-m-d H:i:s')
                ),
                412
            );
        }
    }
}
