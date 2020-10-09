<?php


namespace App\Handler\MessageHandler\Slack;


use App\Entity\Slack\SlashCommand;
use App\Entity\Timer;use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Repository\TimerRepository;
use App\Services\DatabaseHelper;
use App\Services\Time;

class TimerHandler
{

    private TimerRepository $timeEntryRepo;

    private Time $time;

    private DatabaseHelper $databaseHelper;

    private TimerRepository $timerRepository;

    public function __construct(
        Time $time,
        TimerRepository $timeEntryRepo,
        DatabaseHelper $databaseHelper,
        TimerRepository $timerRepository
    )
    {
        $this->timeEntryRepo = $timeEntryRepo;
        $this->time = $time;
        $this->databaseHelper = $databaseHelper;
        $this->timerRepository = $timerRepository;
    }

    public function startTimer(User $user, string $commandStr): Timer
    {
        $timer = $this->timerRepository->findRunningTimer($user);
        if ($timer) {
            $this->time->stopTimer($user, $timer);
        }
        $timerType = str_replace('/', '', $commandStr);
        return $this->time->startTimer($user, $timerType);
    }

    public function stopTimer(User $user, string $taskDescription = null): Timer
    {
        $timer = $this->timeEntryRepo->findRunningTimer($user);
        $this->throwWhenMissingTimer($timer);

        if ($taskDescription && $timer) {
            $timer = $this->time->addTaskToTimer($timer, $taskDescription);
        }

        return $this->time->stopTimer($user, $timer);
    }

    public function lateSignIn(User $user, string $timeStr): Timer
    {
        $this->throwOnExistingTimerFromToday($user);
        $timer = $this->time->startTimerFromTimeString($user, $timeStr, TimerType::WORK);
        $this->databaseHelper->flushAndPersist($timer);
        return $timer;
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
