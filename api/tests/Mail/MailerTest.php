<?php

namespace App\Tests\Mail;

use App\Entity\User;
use App\Mail\Mailer;
use App\Services\Time;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerTest extends TestCase
{
    use ProphecyTrait;

    private $mailerInterface;

    private $logger;

    private $time;

    private Mailer $appMailer;

    private $user;

    public function setup(): void
    {
        $this->mailerInterface = $this->prophesize(MailerInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->time = $this->prophesize(Time::class);
        $this->user = $this->prophesize(User::class);
        $this->appMailer = new Mailer(
            $this->mailerInterface->reveal(),
            $this->logger->reveal(),
            $this->time->reveal()
        );
    }

    public function testSendMail()
    {
        $this->mailerInterface->send(Argument::type(Email::class))
            ->shouldBeCalled();
        $this->appMailer->send('ndilthey@gmail.com', 'yo@time.de', 'Subject', 'Content');
    }

    public function testSendDailySummaryMail()
    {
        $this->time->formatSecondsAsHoursAndMinutes(600)
            ->shouldBeCalledTimes(2)
            ->willReturn('0h 20min');
        $this->user->getFullName()
            ->shouldBeCalledTimes(2)
            ->willReturn('Norman Dilthey');

        $this->mailerInterface->send(Argument::type(Email::class))
                     ->shouldBeCalled();
        $this->appMailer->sendDailySummaryMail(600, 1200, $this->user->reveal(), 'summary');
    }
}
