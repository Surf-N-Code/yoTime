<?php

namespace App\Entity\Slack;

use App\Entity\Timer;

class PunchTimerStatusDto
{

    private string $didSignOut;

    private Timer $timer;


    public function __construct($didSignOut, Timer $timer)
    {
        $this->didSignOut = $didSignOut;
        $this->timer = $timer;
    }

    public function getTimer(): Timer
    {
        return $this->timer;
    }

    public function setTimer(Timer $timer): void
    {
        $this->timer = $timer;
    }

    public function didSignOut(): bool
    {
        return $this->didSignOut;
    }

    public function setDidSignOut(bool $didSignOut): void
    {
        $this->didSignOut = $didSignOut;
    }

}
