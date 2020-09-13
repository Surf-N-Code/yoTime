<?php

namespace App\Entity\Slack;

class ModalSubmissionDto
{

    public const STATUS_ERROR = 'error';
    public const STATUS_SUCCESS = 'success';

    private string $status;

    private string $message;

    private string $title;

    public function __construct($status, $message, $title)
    {
        $this->status = $status;
        $this->message = $message;
        $this->title = $title;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message): void
    {
        $this->message = $message;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status): void
    {
        $this->status = $status;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title): void
    {
        $this->title = $title;
    }

}
