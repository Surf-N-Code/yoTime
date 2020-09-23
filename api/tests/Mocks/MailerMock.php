<?php


namespace App\Tests\Mocks;


use App\Entity\User;
use App\Mail\Mailer;
use App\Services\Time;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;

class MailerMock extends Mailer
{

    public function __construct(
        MailerInterface $mailer,
        LoggerInterface $logger,
        Time $time
    ) {
        parent::__construct($mailer, $logger, $time);
    }

    public function send($from, $to, $subject, $content): void
    {
    }

    public function sendDailySummaryMail(int $timeOnBreak, int $timeOnWork, User $user, string $summary)
    {
        return true;
    }
}
