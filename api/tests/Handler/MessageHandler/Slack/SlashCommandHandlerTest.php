<?php

namespace App\Tests\Handler\MessageHandler\Slack;

use App\Entity\Slack\SlackMessage;
use App\Entity\Slack\SlashCommand;
use App\Entity\Timer;
use App\Entity\TimerType;
use App\Entity\User;
use App\Handler\MessageHandler\Slack\DailySummaryHandler;
use App\Handler\MessageHandler\Slack\SlashCommandHandler;
use App\Handler\MessageHandler\Slack\TimerHandler;
use App\Handler\MessageHandler\Slack\UserHelpHandler;
use App\Mail\Mailer;
use App\Repository\TimerRepository;
use App\Services\DatabaseHelper;
use App\Services\DateTimeProvider;
use App\Services\Time;
use App\Services\UserProvider;
use App\Slack\SlackClient;
use App\Slack\SlackClientMock;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class SlashCommandHandlerTest extends TestCase
{
    use ProphecyTrait;

    private $em;
    private $time;
    private $user;
    private $timeEntryProphecy;
    private $timerHandler;
    private $userProvider;
    private SlashCommandHandler $slashCommandHandler;
    private $databaseHelper;
    private $userHelpHandler;
    private $dailySummaryHandler;
    private $slackClient;
    private $sc;

    public function setup(): void
    {
        $this->user = $this->prophesize(User::class);
        $this->time = $this->prophesize(Time::class);
        $this->timeEntryProphecy = $this->prophesize(Timer::class);
        $this->userProvider = $this->prophesize(UserProvider::class);
        $this->databaseHelper = $this->prophesize(DatabaseHelper::class);
        $this->userHelpHandler = $this->prophesize(UserHelpHandler::class);
        $this->timerHandler = $this->prophesize(TimerHandler::class);
        $this->dailySummaryHandler = $this->prophesize(DailySummaryHandler::class);
        $this->mailer = $this->prophesize(Mailer::class);
        $this->slackMessage = $this->prophesize(SlackMessage::class);
        $this->slackClient = $this->prophesize(SlackClient::class);

        $this->sc = $this->prophesize(SlashCommand::class);

        $this->sc->getUserId()
           ->shouldBeCalled()
           ->willReturn('user1');

        $this->sc->getResponseUrl()
           ->shouldBeCalled()
           ->willReturn('https://www.slack.com/api');

        $this->slashCommandHandler = new SlashCommandHandler(
            $this->userHelpHandler->reveal(),
            $this->dailySummaryHandler->reveal(),
            $this->timerHandler->reveal(),
            $this->userProvider->reveal(),
            $this->databaseHelper->reveal(),
            $this->time->reveal(),
            $this->slackClient->reveal()
        );
    }

    public function testWorkCommand()
    {
        $this->sc->getCommand()
                 ->shouldBeCalled()
                 ->willReturn("/work");

        $this->sc->getText()
                 ->shouldBeCalled()
                 ->willReturn("");

        $this->userProvider->getDbUserBySlackId('user1')
            ->shouldBeCalled()
            ->willReturn($this->user->reveal());

        $this->timerHandler->startTimer($this->user->reveal(), '/work')
            ->shouldBeCalled()
            ->willReturn($this->timeEntryProphecy->reveal());

        $this->databaseHelper->flushAndPersist($this->timeEntryProphecy->reveal())
            ->shouldBeCalled();

        $this->timeEntryProphecy->getTimerType()
            ->shouldBeCalled()
            ->willReturn('work');

        $this->slashCommandHandler->getSlashCommandToExecute($this->sc->reveal());
    }

    public function testBreakCommand()
    {
        $this->sc->getCommand()
                  ->shouldBeCalled()
                  ->willReturn("/break");

        $this->sc->getText()
                 ->shouldBeCalled()
                 ->willReturn("");

        $this->userProvider->getDbUserBySlackId('user1')
                           ->shouldBeCalled()
                           ->willReturn($this->user->reveal());

        $this->timerHandler->startTimer($this->user->reveal(), '/break')
                           ->shouldBeCalled()
                           ->willReturn($this->timeEntryProphecy->reveal());

        $this->timeEntryProphecy->getTimerType()
                                ->shouldBeCalled()
                                ->willReturn('break');

        $this->databaseHelper->flushAndPersist($this->timeEntryProphecy->reveal())
                             ->shouldBeCalled();

        $this->slashCommandHandler->getSlashCommandToExecute($this->sc->reveal());
    }

    public function testLateHiCommand()
    {
        $this->sc->getCommand()
           ->shouldBeCalled()
           ->willReturn("/late_hi");

        $this->sc->getText()
           ->shouldBeCalled()
           ->willReturn("07:30");

        $this->userProvider->getDbUserBySlackId('user1')
                           ->shouldBeCalled()
                           ->willReturn($this->user->reveal());

        $this->timerHandler->lateSignIn($this->user->reveal(), '07:30')
                           ->shouldBeCalled()
                           ->willReturn($this->timeEntryProphecy->reveal());

        $this->timeEntryProphecy->getDateStart()
                                ->shouldBeCalled();

        $this->timeEntryProphecy->getDateStart()
            ->shouldBeCalled()
            ->willReturn(new \DateTime('now'));

        $this->databaseHelper->flushAndPersist($this->timeEntryProphecy->reveal())
                             ->shouldBeCalled();

        $this->slashCommandHandler->getSlashCommandToExecute($this->sc->reveal());
    }

    public function testLateBreakCommand()
    {
        $this->sc->getCommand()
           ->shouldBeCalled()
           ->willReturn("/late_break");

        $this->sc->getText()
           ->shouldBeCalled()
           ->willReturn("07:30");

        $this->userProvider->getDbUserBySlackId('user1')
                           ->shouldBeCalled()
                           ->willReturn($this->user->reveal());

        $this->time->addFinishedTimer($this->user->reveal(), TimerType::BREAK, '07:30')
                           ->shouldBeCalled()
                           ->willReturn($this->timeEntryProphecy->reveal());

        $this->databaseHelper->flushAndPersist($this->timeEntryProphecy->reveal())
                             ->shouldBeCalled();

        $this->slashCommandHandler->getSlashCommandToExecute($this->sc->reveal());
    }

    public function testEndBreakCommand()
    {
        $this->sc->getCommand()
           ->shouldBeCalled()
           ->willReturn("/end_break");

        $this->sc->getText()
           ->shouldBeCalled()
           ->willReturn("Task desc");

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

        $this->databaseHelper->flushAndPersist($this->timeEntryProphecy->reveal())
                             ->shouldBeCalled();

        $this->slashCommandHandler->getSlashCommandToExecute($this->sc->reveal());
    }

    public function testEndWorkCommand()
    {
        $this->sc->getCommand()
           ->shouldBeCalled()
           ->willReturn("/end_work");

        $this->sc->getText()
           ->shouldBeCalled()
           ->willReturn("Task desc");

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

        $this->databaseHelper->flushAndPersist($this->timeEntryProphecy->reveal())
                             ->shouldBeCalled();

        $this->slashCommandHandler->getSlashCommandToExecute($this->sc->reveal());
    }

    public function testAddDailySummaryCommand()
    {
        $this->sc->getCommand()
           ->shouldBeCalled()
           ->willReturn("/ds");

        $this->sc->getText()
           ->shouldBeCalled()
           ->willReturn("summary");

        $this->sc->getTriggerId()
                 ->shouldBeCalled()
                 ->willReturn("234.234.234");

        $this->userProvider->getDbUserBySlackId('user1')
                           ->shouldBeCalled()
                           ->willReturn($this->user->reveal());

        $this->dailySummaryHandler->getDailySummarySubmitView('234.234.234')
                                  ->shouldBeCalled()
                                  ->willReturn([]);

        $this->slackClient->slackApiCall('POST', 'views.open', [])
            ->shouldBeCalled();

        $this->slashCommandHandler->getSlashCommandToExecute($this->sc->reveal());
    }
}
