<?php


namespace App\ObjectFactories;


use App\Entity\Timer;
use App\Entity\User;

class TimerFactory
{
    public function updateTimerObject(
        $timerType,
        User $user,
        Timer $timeEntry,
        \DateTime $dateStart,
        \DateTime $dateEnd = null
    ): Timer
    {
        $timeEntry->setDateStart($dateStart);
        $timeEntry->setDateEnd($dateEnd);
        $timeEntry->setTimerType($timerType);
        $timeEntry->setUser($user);
        return $timeEntry;
    }

    public function createTimerObject(
        $timerType,
        User $user,
        \DateTime $dateStart,
        \DateTime $dateEnd = null
    ): Timer
    {
        $timeEntry = new Timer();
        $timeEntry->setDateStart($dateStart);
        $timeEntry->setDateEnd($dateEnd);
        $timeEntry->setTimerType($timerType);
        $timeEntry->setUser($user);
        return $timeEntry;
    }
}
