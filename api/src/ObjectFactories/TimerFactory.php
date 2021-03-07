<?php


namespace App\ObjectFactories;


use App\Entity\Timer;
use App\Entity\User;

class TimerFactory
{
    public function createTimerObject(
        $timerType,
        User $user,
        \DateTime $dateStart,
        \DateTime $dateEnd = null
    ): Timer
    {
        $timer = new Timer();
        $timer->setDateStart($dateStart);
        $timer->setDateEnd($dateEnd);
        $timer->setTimerType($timerType);
        $timer->setUser($user);
        return $timer;
    }
}
