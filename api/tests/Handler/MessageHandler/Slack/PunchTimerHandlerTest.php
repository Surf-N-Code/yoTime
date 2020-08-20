<?php


namespace App\Tests\Handler\MessageHandler\Slack;


use App\Entity\Timer;
use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Handler\MessageHandler\Slack\PunchTimerHandler;
use App\Repository\TimerRepository;
use App\Services\DatabaseHelper;use App\Services\DateTimeProvider;
use App\Services\Time;
use App\Services\UserProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class PunchTimerHandlerTest extends TestCase
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
    private $punchTimerHandler;
    private $databaseHelper;

    public function setup(): void
    {
        $this->timeEntryRepository = $this->prophesize(TimerRepository::class);
        $this->user = $this->prophesize(User::class);
        $this->time = $this->prophesize(Time::class);
        $this->dateTimeProvider = $this->prophesize(DateTimeProvider::class);
        $this->timeEntryProphecy = $this->prophesize(Timer::class);
        $this->userProvider = $this->prophesize(UserProvider::class);
        $this->databaseHelper = $this->prophesize(DatabaseHelper::class);

        $this->punchTimerHandler = new PunchTimerHandler(
            $this->time->reveal(),
            $this->timeEntryRepository->reveal(),
            $this->databaseHelper->reveal()
        );
    }

    private function buildUser(): User
    {
        $user = new User();
        $user->setSlackUserId('Norman');
        return $user;
    }

    public function testPunchIn()
    {
        $this->timeEntryRepository->findPunchTimer($this->user->reveal())
                                  ->shouldBeCalled()
                                  ->willReturn(null);

        $this->time->stopNonPunchTimers($this->user->reveal())->shouldBeCalled();
        $this->time->startTimer($this->user->reveal(), TimerType::PUNCH)
                   ->shouldBeCalled();
        $this->punchTimerHandler->punchIn($this->user->reveal());
    }

    public function testPunchInAlreadyPunchedIn()
    {
        $this->timeEntryRepository->findPunchTimer($this->user->reveal())
                                  ->shouldBeCalled()
                                  ->willReturn($this->timeEntryProphecy->reveal());

        $this->timeEntryProphecy->setDateStart(new \DateTime('2019-09-09 18:33:00'));
        $this->timeEntryProphecy->getDateStart()
                  ->shouldBeCalled()
                  ->willReturn(new \DateTime('2019-09-09 18:33:00'));

        $this->expectException(MessageHandlerException::class);
        $this->punchTimerHandler->punchIn($this->user->reveal());
    }

    public function testPunchOut()
    {
        $this->timeEntryRepository->findPunchTimer($this->user->reveal())
                            ->shouldBeCalled()
                            ->willReturn($this->timeEntryProphecy->reveal());

        $this->time->stopTimer($this->user->reveal(), $this->timeEntryProphecy->reveal())
                   ->shouldBeCalled();
        $this->punchTimerHandler->punchOut($this->user->reveal());
    }

    public function testPunchOutMissingPunchIn()
    {
        $this->timeEntryRepository->findPunchTimer($this->user->reveal())
                            ->shouldBeCalled()
                            ->willReturn(null);

        $this->expectException(MessageHandlerException::class);
        $this->punchTimerHandler->punchOut($this->user->reveal());
    }

    public function testPunchInAtTime()
    {
        $this->timeEntryRepository->findPunchTimer($this->user->reveal())
                                  ->shouldBeCalled()
                                  ->willReturn(null);

        $this->time->stopNonPunchTimers($this->user->reveal())
            ->shouldBeCalled();
        $this->time->startTimerFromTimeString($this->user->reveal(), '08:30', TimerType::PUNCH)
                   ->shouldBeCalled();
        $this->punchTimerHandler->punchInAtTime($this->user->reveal(), '08:30');
    }
}
