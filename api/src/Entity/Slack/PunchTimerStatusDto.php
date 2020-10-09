<?php

namespace App\Entity\Slack;

use App\Entity\Timer;

class PunchTimerStatusDto
{

    private string $actionStatus;

    private Timer $timer;


    public function __construct($didSignOut, Timer $timer)
    {
        $this->actionStatus = $didSignOut;
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

    public function getActionStatus(): string
    {
        return $this->actionStatus;
    }

    public function setActionStatus(string $didSignOut): void
    {
        $this->actionStatus = $didSignOut;
    }

}
