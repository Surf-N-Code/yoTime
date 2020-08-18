<?php

namespace App\Handler\MessageHandler\Slack;

use App\Entity\DailySummary;
use App\Entity\Slack\SlackMessage;
use App\Entity\Slack\SlashCommand;
use App\Entity\TimeEntry;
use App\Entity\User;
use App\Mail\Mailer;
use App\Repository\TimeEntryRepository;
use App\Services\DatabaseHelper;
use App\Services\DateTimeProvider;
use App\Services\Time;
use App\Services\UserProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class SlashCommandHandlerTest extends TestCase
{
    use ProphecyTrait;

    private $em;
    private $timeEntryRepository;
    private $time;
    private $user;
    private $dateTimeProvider;
    private $timeEntryProphecy;
    private $timerHandler;
    private $userProvider;
    private $slashCommandHandler;
    private $databaseHelper;
    private $userHelpHandler;
    private $mailer;
    private $dailySummaryHandler;
    private $slackMessage;

    public function setup(): void
    {
        $this->timeEntryRepository = $this->prophesize(TimeEntryRepository::class);
        $this->user = $this->prophesize(User::class);
        $this->time = $this->prophesize(Time::class);
        $this->dateTimeProvider = $this->prophesize(DateTimeProvider::class);
        $this->timeEntryProphecy = $this->prophesize(TimeEntry::class);
        $this->userProvider = $this->prophesize(UserProvider::class);
        $this->databaseHelper = $this->prophesize(DatabaseHelper::class);
        $this->userHelpHandler = $this->prophesize(UserHelpHandler::class);
        $this->timerHandler = $this->prophesize(TimerHandler::class);
        $this->dailySummaryHandler = $this->prophesize(DailySummaryHandler::class);
        $this->mailer = $this->prophesize(Mailer::class);
        $this->slackMessage = $this->prophesize(SlackMessage::class);

        $this->slashCommandHandler = new SlashCommandHandler(
            $this->userHelpHandler->reveal(),
            $this->dailySummaryHandler->reveal(),
            $this->timerHandler->reveal(),
            $this->userProvider->reveal(),
            $this->databaseHelper->reveal(),
            $this->mailer->reveal(),
            $this->time->reveal(),
        );
    }

    public function testWorkCommand()
    {
        $sc = $this->prophesize(SlashCommand::class);
        $sc->getCommand()
            ->shouldBeCalled()
            ->willReturn("/work");

        $sc->getText()
           ->shouldBeCalled()
           ->willReturn("");

        $sc->getUserId()
            ->shouldBeCalled()
            ->willReturn('user1');

        $this->userProvider->getDbUserBySlackId('user1')
            ->shouldBeCalled()
            ->willReturn($this->user->reveal());

        $this->timerHandler->startTimer('/work', $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($this->timeEntryProphecy->reveal());

        $this->timeEntryProphecy->getTimerType()
            ->shouldBeCalled()
            ->willReturn('work');

        $this->slashCommandHandler->getSlashCommandToExecute($sc->reveal());
    }

    public function testBreakCommand()
    {
        $sc = $this->prophesize(SlashCommand::class);
        $sc->getCommand()
           ->shouldBeCalled()
           ->willReturn("/break");

        $sc->getText()
           ->shouldBeCalled()
           ->willReturn("");

        $sc->getUserId()
           ->shouldBeCalled()
           ->willReturn('user1');

        $this->userProvider->getDbUserBySlackId('user1')
                           ->shouldBeCalled()
                           ->willReturn($this->user->reveal());

        $this->timerHandler->startTimer('/break', $this->user->reveal())
                           ->shouldBeCalled()
                           ->willReturn($this->timeEntryProphecy->reveal());

        $this->timeEntryProphecy->getTimerType()
                                ->shouldBeCalled()
                                ->willReturn('break');

        $this->slashCommandHandler->getSlashCommandToExecute($sc->reveal());
    }

    public function testLateHiCommand()
    {
        $sc = $this->prophesize(SlashCommand::class);
        $sc->getCommand()
           ->shouldBeCalled()
           ->willReturn("/late_hi");

        $sc->getText()
           ->shouldBeCalled()
           ->willReturn("07:30");

        $sc->getUserId()
           ->shouldBeCalled()
           ->willReturn('user1');

        $this->userProvider->getDbUserBySlackId('user1')
                           ->shouldBeCalled()
                           ->willReturn($this->user->reveal());

        $this->timerHandler->lateSignIn($this->user->reveal(), '07:30')
                           ->shouldBeCalled()
                           ->willReturn($this->timeEntryProphecy->reveal());

        $this->timeEntryProphecy->getDateStart()
                                ->shouldBeCalled();

        $this->slashCommandHandler->getSlashCommandToExecute($sc->reveal());
    }

    public function testLateBreakCommand()
    {
        $sc = $this->prophesize(SlashCommand::class);
        $sc->getCommand()
           ->shouldBeCalled()
           ->willReturn("/late_break");

        $sc->getText()
           ->shouldBeCalled()
           ->willReturn("07:30");

        $sc->getUserId()
           ->shouldBeCalled()
           ->willReturn('user1');

        $this->userProvider->getDbUserBySlackId('user1')
                           ->shouldBeCalled()
                           ->willReturn($this->user->reveal());

        $this->timerHandler->addBreakManually($this->user->reveal(), '07:30')
                           ->shouldBeCalled()
                           ->willReturn($this->timeEntryProphecy->reveal());

        $this->slashCommandHandler->getSlashCommandToExecute($sc->reveal());
    }

    public function testEndBreakCommand()
    {
        $sc = $this->prophesize(SlashCommand::class);
        $sc->getCommand()
           ->shouldBeCalled()
           ->willReturn("/end_break");

        $sc->getText()
           ->shouldBeCalled()
           ->willReturn("Task desc");

        $sc->getUserId()
           ->shouldBeCalled()
           ->willReturn('user1');

        $this->userProvider->getDbUserBySlackId('user1')
                           ->shouldBeCalled()
                           ->willReturn($this->user->reveal());

        $this->timerHandler->stopTimer($this->user->reveal(), 'Task desc')
                           ->shouldBeCalled()
                           ->willReturn($this->timeEntryProphecy->reveal());

        $this->timeEntryProphecy->getTimerType()
                                ->shouldBeCalled()
                                ->willReturn('break');

        $this->time->formatSecondsAsHoursAndMinutes(3600)
            ->shouldBeCalled()
            ->willReturn('1h 0min');

        $this->timeEntryProphecy->getDateEnd()
                                ->shouldBeCalled()
            ->willReturn(new \DateTime('2020-08-01 13:00:00'));

        $this->timeEntryProphecy->getDateStart()
                                ->shouldBeCalled()
                                ->willReturn(new \DateTime('2020-08-01 12:00:00'));

        $this->timeEntryProphecy->getTimerType()
                                ->shouldBeCalled()
                                ->willReturn('break');


        $this->slashCommandHandler->getSlashCommandToExecute($sc->reveal());
    }

    public function testEndWorkCommand()
    {
        $sc = $this->prophesize(SlashCommand::class);
        $sc->getCommand()
           ->shouldBeCalled()
           ->willReturn("/end_work");

        $sc->getText()
           ->shouldBeCalled()
           ->willReturn("Task desc");

        $sc->getUserId()
           ->shouldBeCalled()
           ->willReturn('user1');

        $this->userProvider->getDbUserBySlackId('user1')
                           ->shouldBeCalled()
                           ->willReturn($this->user->reveal());

        $this->timerHandler->stopTimer($this->user->reveal(), 'Task desc')
                           ->shouldBeCalled()
                           ->willReturn($this->timeEntryProphecy->reveal());

        $this->timeEntryProphecy->getTimerType()
                                ->shouldBeCalled()
                                ->willReturn('work');

        $this->time->formatSecondsAsHoursAndMinutes(3600)
                   ->shouldBeCalled()
                   ->willReturn('1h 0min');

        $this->timeEntryProphecy->getDateEnd()
                                ->shouldBeCalled()
                                ->willReturn(new \DateTime('2020-08-01 13:00:00'));

        $this->timeEntryProphecy->getDateStart()
                                ->shouldBeCalled()
                                ->willReturn(new \DateTime('2020-08-01 12:00:00'));

        $this->timeEntryProphecy->getTimerType()
                                ->shouldBeCalled()
                                ->willReturn('break');


        $this->slashCommandHandler->getSlashCommandToExecute($sc->reveal());
    }

    public function testAddDailySummaryCommand()
    {
        $sc = $this->prophesize(SlashCommand::class);
        $sc->getCommand()
           ->shouldBeCalled()
           ->willReturn("/ds");

        $sc->getText()
           ->shouldBeCalled()
           ->willReturn("summary");

        $sc->getUserId()
           ->shouldBeCalled()
           ->willReturn('user1');

        $this->userProvider->getDbUserBySlackId('user1')
                           ->shouldBeCalled()
                           ->willReturn($this->user->reveal());

        $ds = $this->prophesize(DailySummary::class);
        $this->dailySummaryHandler->addDailySummaryFromSlackCommand('summary', $this->user->reveal())
                                  ->shouldBeCalled()
                                  ->willReturn([$this->slackMessage->reveal(), $ds->reveal()]);
        $ds->getTimeWorkedInS()
            ->shouldBeCalled()
            ->willReturn(3600);

        $ds->getTimeBreakInS()
           ->shouldBeCalled()
           ->willReturn(1800);

        $ds->getDailySummary()
            ->shouldBeCalled()
            ->willReturn('summary');

        $this->mailer->sendDAilySummaryMail(1800, 1800, $this->user->reveal(), 'summary')
            ->shouldBeCalled();

        $this->slashCommandHandler->getSlashCommandToExecute($sc->reveal());
    }

    public function testPersistObject()
    {
        $sc = $this->prophesize(SlashCommand::class);
        $sc->getCommand()
           ->shouldBeCalled()
           ->willReturn("/work");

        $sc->getText()
        ->shouldBeCalled()
        ->willReturn('');

        $sc->getUserId()
           ->shouldBeCalled()
           ->willReturn('user1');

        $this->userProvider->getDbUserBySlackId('user1')
                           ->shouldBeCalled()
                           ->willReturn($this->user->reveal());

        $this->timerHandler->startTimer('/work', $this->user->reveal())
                           ->shouldBeCalled()
                           ->willReturn($this->timeEntryProphecy->reveal());

        $this->timeEntryProphecy->getTimerType()
                                ->shouldBeCalled()
                                ->willReturn('work');

        $this->databaseHelper->flushAndPersist($this->timeEntryProphecy->reveal())
            ->shouldBeCalled();
        $this->slashCommandHandler->getSlashCommandToExecute($sc->reveal());
    }
}
