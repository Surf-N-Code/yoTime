<?php

namespace App\Entity\Slack;

use App\Entity\Timer;

class PunchTimerStatusDto
{

    private string $actionStatus;

    private Timer $timer;


    public function __construct($actionStatus, Timer $timer)
    {
        $this->actionStatus = $actionStatus;
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

    public function setActionStatus(string $actionStatus): void
    {
        $this->actionStatus = $actionStatus;
    }

}
