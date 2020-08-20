<?php

namespace App\Services;

use App\Entity\Task;
use App\Entity\Timer;
use App\Entity\TimerType;use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Repository\TimerRepository;
use App\Repository\UserRepository;
use App\ObjectFactories\TimerFactory;

class Time
{
    private $timeEntryRepository;

    private $userRepository;

    private $dateTimeProvider;

    private $timeEntryFactory;

    public function __construct(
        TimerRepository $timeEntryRepository,
        UserRepository $userRepository,
        DateTimeProvider $dateTimeProvider,
        TimerFactory $timeEntryFactory
    )
    {
        $this->timeEntryRepository = $timeEntryRepository;
        $this->userRepository = $userRepository;
        $this->dateTimeProvider = $dateTimeProvider;
        $this->timeEntryFactory = $timeEntryFactory;
    }

    public function startTimer(User $user, $timerType, \DateTime $dateStart = null): Timer
    {
        $currentUserTime = $this->dateTimeProvider->getLocalUserTime($user);
        return $this->timeEntryFactory->createTimerObject($timerType, $user, $dateStart ?? $currentUserTime);
    }

    public function stopTimer(User $user, Timer $timeEntry): Timer
    {
        $currentUserTime = $this->dateTimeProvider->getLocalUserTime($user);
        $timeEntry->setDateEnd($currentUserTime);
        return $timeEntry;
    }

    public function stopNonPunchTimers(User $user): void
    {
        $allRunningTimers = $this->timeEntryRepository->findNonPunchTimers($user);
        if ($allRunningTimers) {
            foreach ($allRunningTimers as $timeEntry) {
                $this->stopTimer($user, $timeEntry);
            }
        }
    }

    public function addTaskToTimer(Timer $timeEntry, string $taskDescription): Timer
    {
        $task = new Task();
        $task->setDescription($taskDescription);
        $timeEntry->setTask($task);
        return $timeEntry;
    }

    public function startTimerFromTimeString(User $user, $timeString, $timerType): Timer
    {
        preg_match('/^([01]?\d|2[0-3]):?([0-5]\d)/', $timeString, $militaryTime);
        if (!empty($militaryTime)) {
            $startDate = \DateTime::createFromFormat('Y-m-d H:i', date('Y-m-d').' '.$militaryTime[1].':'.$militaryTime[2]);
            return $this->startTimer($user, $timerType, $startDate);
        }

        preg_match('/([0-9]|([1][0-2])):[0-5][0-9][[:space:]]?([ap][m]?|[AP][M]?)/', $timeString, $civilTime);
        if (!empty($civilTime)) {
            $startDate = \DateTime::createFromFormat('Y-m-d H:i A', date('Y-m-d').' '.$civilTime[0]);
            return $this->startTimer($user, $timerType, $startDate);
        }

        throw new MessageHandlerException(sprintf('The time you entered: %s is not valid. Please enter your time in the form `hh:mm`, e.g.: `14:21`', $timeString), 412);
    }

    public function addFinishedTimer(User $user, string $timerType, array $timeParts): Timer
    {
        $dateStart = (new \DateTime($this->dateTimeProvider->getLocalUserTime($user)->format('Y-m-d H:i:s')))->setTime(1,0,0);
        $dateEnd = clone($dateStart);
        $dateEnd->add(new \DateInterval(sprintf('PT%sH%sM', $timeParts[0], $timeParts[1])));
        return $this->timeEntryFactory->createTimerObject($timerType, $user, $dateStart, $dateEnd);
    }

    public function getHoursAndMinutesFromString(string $str)
    {
        preg_match('/^([01]?\d|2[0-3]):([0-5]\d)/', $str, $durationMatch);
        if (empty($durationMatch)) {
            throw new MessageHandlerException(sprintf('The time you entered: %s is not valid. Please enter your time in a valid military (24H) format `hh:mm` like 08:30', $str), 412);
        }
        return explode(':', $str);
    }

    public function formatSecondsAsHoursAndMinutes(int $seconds): string
    {
        $diff = round($seconds / 3600,2);
        $parts = explode('.', $diff);
        $minutes = 00;
        if (count($parts) > 1) {
            $minutes = round($parts[1]/100 * 60,0);
        }

        return  sprintf("%sh %smin", $parts[0], $minutes);
    }

    public function getTimeSpentOnTypeByPeriod(User $user, string $period, $timerType, string $slackUserId = null)
    {
        $timeConstraintsFormat = [
            'day' => 'day',
            'week' => 'week',
            'month' => 'month',
            'year' => 'year',
            'all' => 'all',
        ];

        if (!array_key_exists(trim($period), $timeConstraintsFormat)) {
            throw new MessageHandlerException(sprintf('The time period you entered: `%s` is not valid', $period), 412);
        }

        if ($slackUserId) {
            $user = $this->userRepository->findOneBy([
                'slackUserId' => $slackUserId,
            ]);
        }

        try {
            $timeEntries = $this->timeEntryRepository->findTimeEntriesByPeriod(
                $user,
                $this->dateTimeProvider->getLocalUserTime($user),
                $period,
                false,
                $timerType
            );
        } catch (\Exception $e) {
            throw new MessageHandlerException('An error occured, please contact support', 412);
        }

        $s = 0;
        foreach ($timeEntries as $entry) {
            if (!$entry->getDateEnd()) {
                //currently running timer
                $tE = $this->dateTimeProvider->getLocalUserTime($user)->getTimestamp();
            } else {
                $tE = $entry->getDateEnd()->getTimestamp();
            }

            $s += abs($tE - $entry->getDateStart()->getTimestamp());
        }

        return (int) $s;
    }

    public function getWorktimeByPeriod(User $user, $period)
    {
        $work = $this->getTimeSpentOnTypeByPeriod($user, $period, TimerType::WORK);
        $break = $this->getTimeSpentOnTypeByPeriod($user, $period, TimerType::BREAK);

        return $work - $break;
    }
}
