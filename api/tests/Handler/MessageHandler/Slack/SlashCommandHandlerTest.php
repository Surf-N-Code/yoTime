<?php

namespace App\Tests\Handler\MessageHandler\Slack;

use App\Entity\Slack\SlackMessage;
use App\Entity\Slack\SlashCommand;
use App\Entity\Timer;
use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Handler\MessageHandler\Slack\DailySummaryHandler;
use App\Handler\MessageHandler\Slack\RegisterHandler;
use App\Handler\MessageHandler\Slack\ReportingHandler;
use App\Handler\MessageHandler\Slack\SlashCommandHandler;
use App\Handler\MessageHandler\Slack\TimerHandler;
use App\Handler\MessageHandler\Slack\UserHelpHandler;
use App\Mail\Mailer;
use App\Services\DatabaseHelper;
use App\Services\Time;
use App\Services\UserProvider;
use App\Slack\SlackClient;
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
    private $registerHandler;
    private $dailySummaryHandler;
    private $reportingHandler;
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
        $this->registerHandler = $this->prophesize(RegisterHandler::class);
        $this->timerHandler = $this->prophesize(TimerHandler::class);
        $this->dailySummaryHandler = $this->prophesize(DailySummaryHandler::class);
        $this->reportingHandler = $this->prophesize(ReportingHandler::class);
        $this->mailer = $this->prophesize(Mailer::class);
        $this->slackMessage = $this->prophesize(SlackMessage::class);
        $this->slackClient = $this->prophesize(SlackClient::class);

        $this->sc = $this->prophesize(SlashCommand::class);

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
            $this->slackClient->reveal(),
            $this->registerHandler->reveal(),
            $this->reportingHandler->reveal()
        );
    }

    public function testWorkCommand()
    {
        $this->sc->getCommand()
                 ->shouldBeCalled()
                 ->willReturn(SlashCommandHandler::START_WORK);

        $this->sc->getUserId()
                 ->shouldBeCalled()
                 ->willReturn('user1');

        $this->sc->getText()
                 ->shouldBeCalled()
                 ->willReturn("");

        $this->userProvider->getDbUserBySlackId('user1')
            ->shouldBeCalled()
            ->willReturn($this->user->reveal());

        $this->timerHandler->startTimer($this->user->reveal(), SlashCommandHandler::START_WORK)
            ->shouldBeCalled()
            ->willReturn($this->timeEntryProphecy->reveal());

        $this->databaseHelper->flushAndPersist($this->timeEntryProphecy->reveal())
            ->shouldBeCalled();

        $this->timeEntryProphecy->getTimerType()
            ->shouldBeCalled()
            ->willReturn(TimerType::WORK);

        $this->slashCommandHandler->getSlashCommandToExecute($this->sc->reveal());
    }

    public function testBreakCommand()
    {
        $this->sc->getCommand()
                  ->shouldBeCalled()
                  ->willReturn(SlashCommandHandler::START_BREAK);

        $this->sc->getUserId()
                 ->shouldBeCalled()
                 ->willReturn('user1');

        $this->sc->getText()
                 ->shouldBeCalled()
                 ->willReturn("");

        $this->userProvider->getDbUserBySlackId('user1')
                           ->shouldBeCalled()
                           ->willReturn($this->user->reveal());

        $this->timerHandler->startTimer($this->user->reveal(),  SlashCommandHandler::START_BREAK)
                           ->shouldBeCalled()
                           ->willReturn($this->timeEntryProphecy->reveal());

        $this->timeEntryProphecy->getTimerType()
                                ->shouldBeCalled()
                                ->willReturn(TimerType::BREAK);

        $this->databaseHelper->flushAndPersist($this->timeEntryProphecy->reveal())
                              ->shouldBeCalled();

        $this->slashCommandHandler->getSlashCommandToExecute($this->sc->reveal());
    }

    public function testLateHiCommand()
    {
        $this->sc->getCommand()
           ->shouldBeCalled()
           ->willReturn(SlashCommandHandler::LATE_HI);

        $this->sc->getText()
           ->shouldBeCalled()
           ->willReturn("07:30");

        $this->sc->getUserId()
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
           ->willReturn(SlashCommandHandler::LATE_BREAK);

        $this->sc->getText()
           ->shouldBeCalled()
           ->willReturn("07:30");

        $this->sc->getUserId()
                 ->shouldBeCalled()
                 ->willReturn('user1');

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
           ->willReturn(SlashCommandHandler::STOP_TIMER);

        $this->sc->getText()
           ->shouldBeCalled()
           ->willReturn("Task desc");

        $this->sc->getUserId()
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
                                ->willReturn(TimerType::BREAK);

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
                                ->willReturn(TimerType::BREAK);

        $this->databaseHelper->flushAndPersist($this->timeEntryProphecy->reveal())
                             ->shouldBeCalled();

        $this->slashCommandHandler->getSlashCommandToExecute($this->sc->reveal());
    }

    public function testEndWorkCommand()
    {
        $this->sc->getCommand()
           ->shouldBeCalled()
           ->willReturn(SlashCommandHandler::STOP_TIMER);

        $this->sc->getText()
           ->shouldBeCalled()
           ->willReturn("Task desc");

        $this->sc->getUserId()
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
                                ->willReturn(TimerType::BREAK);

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
                                ->willReturn(TimerType::BREAK);

        $this->databaseHelper->flushAndPersist($this->timeEntryProphecy->reveal())
                             ->shouldBeCalled();

        $this->slashCommandHandler->getSlashCommandToExecute($this->sc->reveal());
    }

    public function testAddDailySummaryCommand()
    {
        $this->sc->getCommand()
           ->shouldBeCalled()
           ->willReturn(SlashCommandHandler::DAILY_SUMMARY);

        $this->sc->getText()
           ->shouldBeCalled()
           ->willReturn("summary");

        $this->sc->getTriggerId()
                 ->shouldBeCalled()
                 ->willReturn("234.234.234");

        $this->dailySummaryHandler->getDailySummarySubmitView('234.234.234')
                                  ->shouldBeCalled()
                                  ->willReturn([]);

        $this->slackClient->slackApiCall('POST', 'views.open', [])
            ->shouldBeCalled();

        $this->slashCommandHandler->getSlashCommandToExecute($this->sc->reveal());
    }

    public function testRegisterCommand()
    {
        $this->sc->getCommand()
                 ->shouldBeCalled()
                 ->willReturn(SlashCommandHandler::REGISTER);

        $this->sc->getText()
                 ->shouldBeCalled()
                 ->willReturn('day');

        $this->sc->getUserId()
                 ->shouldBeCalled()
                 ->willReturn('user1');

        $this->userProvider->getSlackUser('user1')
                           ->shouldBeCalled()
                           ->willReturn($this->user->reveal());

        $this->userProvider->populateUserEntityFromSlackInfo($this->user->reveal())
                           ->shouldBeCalled()
                           ->willReturn($this->user->reveal());

        $this->slashCommandHandler->getSlashCommandToExecute($this->sc->reveal());
    }

}
