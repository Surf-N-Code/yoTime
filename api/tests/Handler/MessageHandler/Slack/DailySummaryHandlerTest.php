<?php


namespace App\Tests\Handler\MessageHandler\Slack;


use App\Entity\DailySummary;
use App\Entity\Slack\SlackMessage;use App\Entity\Slack\SlashCommand;
use App\Entity\Timer;use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;use App\Handler\MessageHandler\Slack\DailySummaryHandler;
use App\Handler\MessageHandler\Slack\PunchTimerHandler;
use App\ObjectFactories\DailySummaryFactory;use App\Repository\DailySummaryRepository;
use App\Services\DatabaseHelper;
use App\Mail\Mailer;
use App\Services\Time;
use App\Slack\SlackMessageHelper;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class DailySummaryHandlerTest extends TestCase
{
    use ProphecyTrait;

    private $time;
    private $user;
    private $dailySummaryRepo;
    private DailySummaryHandler $dailySummaryHandler;
    private $punchTimerHandler;
    private $slackMessageHelper;
    private $timeEntryProphecy;
    private $dailySummaryProphecy;
    private $dailySummaryFactory;
    private $slackMessage;

    public function setup(): void
    {
        $this->timeEntryProphecy = $this->prophesize(Timer::class);
        $this->user = $this->prophesize(User::class);
        $this->dailySummaryRepo = $this->prophesize(DailySummaryRepository::class);
        $this->time = $this->prophesize(Time::class);
        $this->punchTimerHandler = $this->prophesize(PunchTimerHandler::class);
        $this->slackMessageHelper = $this->prophesize(SlackMessageHelper::class);
        $this->dailySummaryProphecy = $this->prophesize(DailySummary::class);
        $this->dailySummaryFactory = $this->prophesize(DailySummaryFactory::class);
        $this->slackMessage = $this->prophesize(SlackMessage::class);

        $this->dailySummaryHandler = new DailySummaryHandler(
            $this->punchTimerHandler->reveal(),
            $this->dailySummaryRepo->reveal(),
            $this->time->reveal(),
            $this->dailySummaryFactory->reveal()
        );
    }

    public function testAddDailySummaryFromSlackCommandWithPunchout()
    {
        $sc = $this->prophesize(SlashCommand::class);
        $sc->getText()->shouldBeCalled()->willReturn('Example Summary');

        $this->punchTimerHandler->punchOut($this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($this->timeEntryProphecy->reveal());

        $this->time->getTimeSpentOnTypeByPeriod($this->user->reveal(), 'day', TimerType::WORK)
            ->shouldBeCalled()->willReturn(3600);

        $this->time->getTimeSpentOnTypeByPeriod($this->user->reveal(), 'day', TimerType::BREAK)
            ->shouldBeCalled()->willReturn(1800);

        $this->time->formatSecondsAsHoursAndMinutes(1800)
            ->shouldBeCalledTimes(2)->willReturn('0h 30min');

        $this->dailySummaryRepo->findOneBy(Argument::type('array'))
                               ->shouldBeCalled()
                               ->willReturn(null);
        $this->dailySummaryFactory->createDailySummaryObject('Example Summary', null, $this->user->reveal(), 3600, 1800)
            ->shouldBeCalled()
            ->willReturn($this->dailySummaryProphecy->reveal());

        $this->dailySummaryHandler->addDailySummaryFromSlackCommand($sc->reveal(), $this->user->reveal());
    }

    public function testAddDailySummaryFromSlackCommandWithoutPunchout()
    {
        $sc = $this->prophesize(SlashCommand::class);
        $sc->getText()->shouldBeCalled()->willReturn('Example Summary');

        $this->punchTimerHandler->punchOut($this->user->reveal())
            ->shouldBeCalled()
            ->willThrow(MessageHandlerException::class);

        $this->expectException(MessageHandlerException::class);
        $this->dailySummaryHandler->addDailySummaryFromSlackCommand($sc->reveal(), $this->user->reveal());
    }

    public function testAddDailySummaryFromSlackCommandUpdateDs()
    {
        $sc = $this->prophesize(SlashCommand::class);
        $sc->getText()->shouldBeCalled()->willReturn('Example Summary');

        $this->punchTimerHandler->punchOut($this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($this->timeEntryProphecy->reveal());

        $this->time->getTimeSpentOnTypeByPeriod($this->user->reveal(), 'day', TimerType::WORK)
            ->shouldBeCalled()->willReturn(3600);

        $this->time->getTimeSpentOnTypeByPeriod($this->user->reveal(), 'day', TimerType::BREAK)
            ->shouldBeCalled()->willReturn(1800);

        $this->time->formatSecondsAsHoursAndMinutes(1800)
            ->shouldBeCalledTimes(2)->willReturn('0h 30min');

        $this->dailySummaryRepo->findOneBy(Argument::type('array'))
                               ->shouldBeCalled()
                               ->willReturn($this->dailySummaryProphecy->reveal());
        $this->dailySummaryFactory->createDailySummaryObject('Example Summary', $this->dailySummaryProphecy->reveal(), $this->user->reveal(), 3600, 1800)
            ->shouldBeCalled()
            ->willReturn($this->dailySummaryProphecy->reveal());

        $this->dailySummaryHandler->addDailySummaryFromSlackCommand($sc->reveal(), $this->user->reveal());
    }

    public function testAddDailySummaryFromSlackCommandMissingSummary()
    {
        $sc = $this->prophesize(SlashCommand::class);
        $sc->getText()->shouldBeCalled()->willReturn('');

        $this->expectException(MessageHandlerException::class);

        $this->dailySummaryHandler->addDailySummaryFromSlackCommand($sc->reveal(), $this->user->reveal());
    }
}
