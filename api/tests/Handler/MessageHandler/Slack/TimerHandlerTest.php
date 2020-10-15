<?php


namespace App\Tests\Handler\MessageHandler\Slack;


use App\Entity\Timer;
use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Handler\MessageHandler\Slack\TimerHandler;
use App\Repository\TimerRepository;
use App\Services\DatabaseHelper;
use App\Services\Time;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class TimerHandlerTest extends TestCase
{
    use ProphecyTrait;

    private $em;
    private $timeEntryRepository;
    private $time;
    private $user;
    private $timer;
    private $databaseHelper;
    private TimerHandler $timerHandler;

    public function setup(): void
    {
        $this->timeEntryRepository = $this->prophesize(TimerRepository::class);
        $this->user = $this->prophesize(User::class);
        $this->time = $this->prophesize(Time::class);
        $this->timer = $this->prophesize(Timer::class);

        $this->timerHandler = new TimerHandler(
            $this->time->reveal(),
            $this->timeEntryRepository->reveal()
        );
    }

    public function testStartWorkTimer()
    {
        $this->timeEntryRepository->findRunningTimer($this->user->reveal())
                                  ->shouldBeCalled()
                                  ->willReturn(null);

        $this->time->startTimer($this->user->reveal(), TimerType::WORK)
                   ->shouldBeCalled()
                   ->willReturn($this->timer->reveal());
        $this->timerHandler->startTimer($this->user->reveal(), '/'.TimerType::WORK);
    }

    public function testStartWorkTimerWithRunningTimer()
    {
        $this->timeEntryRepository->findRunningTimer($this->user->reveal())
                                  ->shouldBeCalled()
                                  ->willReturn($this->timer->reveal());

        $this->time->stopTimer($this->user->reveal(), $this->timer->reveal())
            ->shouldBeCalled();

        $this->time->startTimer($this->user->reveal(), TimerType::WORK)
                   ->shouldBeCalled()
                   ->willReturn($this->timer->reveal());
        $this->timerHandler->startTimer($this->user->reveal(), '/'.TimerType::WORK);
    }

    public function testStopTimer()
    {
        $this->timeEntryRepository->findRunningTimer($this->user->reveal())
                                  ->shouldBeCalled()
                                  ->willReturn($this->timer->reveal());

        $this->time->stopTimer($this->user->reveal(), $this->timer->reveal())
                   ->shouldBeCalled()
                   ->willReturn($this->timer->reveal());

        $this->timerHandler->stopTimer($this->user->reveal());
    }

    public function testStopTimerWithTask()
    {
        $this->timeEntryRepository->findRunningTimer($this->user->reveal())
                                  ->shouldBeCalled()
                                  ->willReturn($this->timer->reveal());

        $this->time->stopTimer($this->user->reveal(), $this->timer->reveal())
                   ->shouldBeCalled()
                   ->willReturn($this->timer->reveal());

        $this->time->addTaskToTimer($this->timer->reveal(), 'My Task')
            ->shouldBeCalled()
            ->willReturn($this->timer->reveal());

        $this->timerHandler->stopTimer($this->user->reveal(), 'My Task');
    }

    public function testStopTimerWithoutRunningTimer()
    {
        $this->timeEntryRepository->findRunningTimer($this->user->reveal())
                                  ->shouldBeCalled()
                                  ->willReturn(null);
        $this->expectException(MessageHandlerException::class);
        $this->timerHandler->stopTimer($this->user->reveal());
    }

    public function testPunchOut()
    {
        $this->timeEntryRepository->findTimersFromToday($this->user->reveal())
                            ->shouldBeCalled()
                            ->willReturn([$this->timer->reveal()]);

        $this->time->stopTimer($this->user->reveal(), $this->timer->reveal())
                   ->shouldBeCalled()
                   ->willReturn($this->timer->reveal());

        $punchTimerStatusDto = $this->timerHandler->punchOut($this->user->reveal());
        self::assertTrue($punchTimerStatusDto->didSignOut());
    }

    public function testPunchOutMissingRunningTimer()
    {
        $date = new \DateTime('now');
        $this->timeEntryRepository->findTimersFromToday($this->user->reveal())
                                  ->shouldBeCalled()
                                  ->willReturn([$this->timer->reveal()]);

        $this->timer->getDateEnd()
                    ->shouldBeCalled()
                    ->willReturn($date);

        $this->time->stopTimer($this->user->reveal(), $this->timer->reveal())
                   ->shouldNotBeCalled();
        $punchTimerStatusDto = $this->timerHandler->punchOut($this->user->reveal());
        self::assertFalse($punchTimerStatusDto->didSignOut());
    }

    public function testPunchOutMissingPunchIn()
    {
        $this->timeEntryRepository->findTimersFromToday($this->user->reveal())
                            ->shouldBeCalled()
                            ->willReturn(null);

        $this->expectException(MessageHandlerException::class);
        $this->timerHandler->punchOut($this->user->reveal());
    }

    public function testLateSignInWithRunningTimer()
    {
        $date = new \DateTime('now');
        $this->timeEntryRepository->findTimersFromToday($this->user->reveal())
            ->shouldBeCalled()
            ->willReturn([$this->timer->reveal()]);

        $this->timer->getDateStart()
                    ->shouldBeCalled()
                    ->willReturn($date);

        $this->expectException(MessageHandlerException::class);
        $this->timerHandler->lateSignIn($this->user->reveal(), '08:30');
    }

    public function testLateSignIn()
    {
        $this->timeEntryRepository->findTimersFromToday($this->user->reveal())
                                  ->shouldBeCalled()
                                  ->willReturn([]);

        $this->time->startTimerFromTimeString($this->user->reveal(), '08:30', TimerType::WORK)
            ->shouldBeCalled()
            ->willReturn($this->timer->reveal());

        $this->timerHandler->lateSignIn($this->user->reveal(), '08:30');
    }
}
