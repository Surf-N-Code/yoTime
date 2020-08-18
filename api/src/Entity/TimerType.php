<?php

namespace App\Entity;

class TimerType
{
    const TYPES = [
        0 => self::WORK,
        1 => self::BREAK,
        2 => self::PUNCH
    ];

    const PUNCH = 'punch';
    const WORK = 'work';
    const BREAK = 'break';

    private $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function setTimerType($type)
    {
        $this->type = $type;
    }

    public function getTimerType(): ?string
    {
        return $this->type;
    }
}
