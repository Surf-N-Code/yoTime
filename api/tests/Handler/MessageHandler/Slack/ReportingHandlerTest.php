<?php

namespace App\Tests\Handler\MessageHandler\Slack;

use App\Entity\Slack\SlashCommand;
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
    private $slashCommand;

    private ReportingHandler $reportinghandler;

    public function setup(): void
    {
        $this->time = $this->prophesize(Time::class);
        $this->user = $this->prophesize(User::class);
        $this->slashCommand = $this->prophesize(SlashCommand::class);
        $this->reportinghandler = new ReportingHandler(
            $this->time->reveal()
        );
    }

    public function testGetStatusByDay()
    {
        $this->time->getTimesSpentByTypeAndPeriod($this->user->reveal(), 'day')
            ->shouldBeCalled()
            ->willReturn(['work' => 3600, 'break' => 600]);

        $this->slashCommand->getText()->shouldBeCalled()->willReturn('day');
        $this->time->formatSecondsAsHoursAndMinutes(3600)
                   ->shouldBeCalled()
                   ->willReturn('1h 0min');

        $this->time->formatSecondsAsHoursAndMinutes(600)
                   ->shouldBeCalled()
                   ->willReturn('0h 10min');
        $this->reportinghandler->getUserReport($this->user->reveal(), $this->slashCommand->reveal());
    }
}
