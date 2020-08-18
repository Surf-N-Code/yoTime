<?php


namespace App\Entity;


class TrackingScheme
{
    const SCHEMES = [
        0 => self::PUNCH,
        1 => self::TASK
    ];

    const PUNCH = 'sign_in_out';
    const TASK = 'tasks';

    private $scheme;

    public function __construct($scheme)
    {
        $this->setScheme($scheme);
    }

    /**
     * @return mixed
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @param mixed $scheme
     */
    public function setScheme($scheme): void
    {
        $this->scheme = $scheme;
    }
}
