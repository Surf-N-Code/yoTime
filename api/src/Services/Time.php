<?php

namespace App\Services;

use App\Entity\Task;
use App\Entity\Timer;
use App\Entity\TimerType;use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Repository\TimerRepository;
use App\ObjectFactories\TimerFactory;
use Symfony\Component\HttpFoundation\Response;

class Time
{
    private $timeEntryRepository;

    private $timerFactory;

    public function __construct(
        TimerRepository $timeEntryRepository,
        TimerFactory $timerFactory
    )
    {
        $this->timeEntryRepository = $timeEntryRepository;
        $this->timerFactory = $timerFactory;
    }

    public function startTimer(User $user, $timerType, \DateTime $dateStart = null): Timer
    {
        return $this->timerFactory->createTimerObject($timerType, $user, $dateStart ?? new \DateTime('now'));
    }

    public function stopTimer(Timer $timer): Timer
    {
        $timer->setDateEnd(new \DateTime('now'));
        return $timer;
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

        $msg = sprintf('The time you entered: %s is not valid. Please enter your time in the form `hh:mm`', $timeString);
        if ($timeString === '') {
            $msg = 'Please provide the time you started work this morning in the form: `hh:mm`';
        }

        throw new MessageHandlerException($msg, 400);
    }

    public function addFinishedTimer(User $user, string $timerType, string $timeString): Timer
    {
        preg_match('/^([01]?\d|2[0-3]):([0-5]\d)/', $timeString, $durationMatch);
        if ($timeString === '') {
            throw new MessageHandlerException('Please provide the amount of time you spent on break in the form: `hh:mm`', Response::HTTP_PRECONDITION_FAILED);
        }
        if (empty($durationMatch)) {
            throw new MessageHandlerException(sprintf('The time you entered: %s is not valid. Please enter your time in the form `hh:mm`', $timeString), Response::HTTP_PRECONDITION_FAILED);
        }
        $timeParts = explode(':', $timeString);

        $dateStart = (new \DateTime('now'))->setTime(1,0,0);
        $dateEnd = clone($dateStart);
        $dateEnd->add(new \DateInterval(sprintf('PT%sH%sM', $timeParts[0], $timeParts[1])));
        return $this->timerFactory->createTimerObject($timerType, $user, $dateStart, $dateEnd);
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

    public function getTimesSpentByTypeAndPeriod(User $user, string $period, ?string $timerType = null)
    {
        $timeConstraintsFormat = [
            'day' => 'day',
            'week' => 'week',
            'month' => 'month',
            'year' => 'year',
            'all' => 'all',
        ];

        if (!array_key_exists(trim($period), $timeConstraintsFormat)) {
            $m = 'The time period you entered: `%s` is not valid';
            if ($period === '') {
                $m = sprintf('Please provide a period for your report. It can be anything from this list: `%s`', implode('`, `', $timeConstraintsFormat));
            }
            throw new MessageHandlerException($m, 400);
        }

        try {
            $timeEntries = $this->timeEntryRepository->findTimeEntriesByPeriod(
                $user,
                $period,
                false,
                $timerType
            );
        } catch (\Exception $e) {
            throw new MessageHandlerException('An error occured, please contact support', 400);
        }

        $s[TimerType::WORK] = 0;
        $s[TimerType::BREAK] = 0;
        foreach ($timeEntries as $timer) {
            if (!$timer->getDateEnd()) {
                //currently running timer
                $tE = (new \DateTime('now'))->getTimestamp();
            } else {
                $tE = $timer->getDateEnd()->getTimestamp();
            }

            $s[$timer->getTimerType()] += abs($tE - $timer->getDateStart()->getTimestamp());
        }

        return $s;
    }
}
