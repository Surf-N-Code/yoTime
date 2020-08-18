<?php


namespace App\ObjectFactories;


use App\Entity\TimeEntry;
use App\Entity\User;

class TimeEntryFactory
{
    public function updateTimeEntryObject(
        $timerType,
        User $user,
        TimeEntry $timeEntry,
        \DateTime $dateStart = null,
        \DateTime $dateEnd = null
    ): TimeEntry
    {
        $timeEntry->setDateStart($dateStart);
        $timeEntry->setDateEnd($dateEnd);
        $timeEntry->setTimerType($timerType);
        $timeEntry->setUser($user);
        return $timeEntry;
    }

    public function createTimeEntryObject(
        $timerType,
        User $user,
        \DateTime $dateStart,
        \DateTime $dateEnd = null
    ): TimeEntry
    {
        $timeEntry = new TimeEntry();
        $timeEntry->setDateStart($dateStart);
        $timeEntry->setDateEnd($dateEnd);
        $timeEntry->setTimerType($timerType);
        $timeEntry->setUser($user);
        return $timeEntry;
    }
}
