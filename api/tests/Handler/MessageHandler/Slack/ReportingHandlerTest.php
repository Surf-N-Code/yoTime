<?php

namespace App\Tests\Handler\MessageHandler\Slack;

use App\Entity\User;
use App\Handler\MessageHandler\Slack\ReportingHandler;
use App\Services\Time;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\Revealer;

class ReportingHandlerTest extends TestCase
{

    use ProphecyTrait;

    private $time;
    private $user;

    private ReportingHandler $reportinghandler;

    public function setup(): void
    {
        $this->time = $this->prophesize(Time::class);
        $this->user = $this->prophesize(User::class);

        $this->reportinghandler = new ReportingHandler(
            $this->time->reveal()
        );
    }

    public function testGetStatusByDay()
    {
        $this->time->getTimesSpentByTypeAndPeriod($this->user->reveal(), 'day')
            ->shouldBeCalled()
            ->willReturn([3600, 600]);
        $this->reportinghandler->getUserReport($this->user->reveal(), '/day');
    }
}
