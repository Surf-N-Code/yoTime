<?php


namespace App\Tests\Services;


use App\Entity\Task;
use App\Entity\Timer;
use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\ObjectFactories\TimerFactory;
use App\Repository\TimerRepository;
use App\Repository\UserRepository;
use App\Services\DateTimeProvider;
use App\Services\Time;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;

class TimeTest extends TestCase
{

    use ProphecyTrait;

    private $em;
    private $logger;
    private $userRepository;
    private $user;
    private $timeProphecy;
    private $time;
    private $task;
    private $timer;
    private $timeEntryRepository;
    private $timerType;
    private $dateTimeProvider;
    private $timerFactory;

    public function setup(): void
    {
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->userRepository = $this->prophesize(UserRepository::class);
        $this->timeEntryRepository = $this->prophesize(TimerRepository::class);
        $this->task = $this->prophesize(Task::class);
        $this->timer = $this->prophesize(Timer::class);
        $this->user = $this->prophesize(User::class);
        $this->timerType = $this->prophesize(TimerType::class);
        $this->timeProphecy = $this->prophesize(Time::class);
        $this->dateTimeProvider = $this->prophesize(DateTimeProvider::class);
        $this->timerFactory = $this->prophesize(TimerFactory::class);

        $this->time = new Time(
            $this->timeEntryRepository->reveal(),
            $this->userRepository->reveal(),
            $this->dateTimeProvider->reveal(),
            $this->timerFactory->reveal()
        );
    }

    public function testStartTimer()
    {
        $date = new \DateTime('now');
        $this->dateTimeProvider->getLocalUserTime($this->user->reveal())
            ->shouldBeCalled()
            ->willReturn((new \DateTime()));

        $this->timerFactory->createTimerObject('work', $this->user->reveal(), $date)
            ->shouldBeCalled()
            ->willReturn($this->timer->reveal());

        $this->time->startTimer(
            $this->user->reveal(),
            TimerType::WORK,
            $date
        );
    }

    public function testStopTimer()
    {
        $date = new \DateTime('now');
        $this->dateTimeProvider->getLocalUserTime($this->user->reveal())
                               ->shouldBeCalled()
                               ->willReturn($date);

        $this->timer->setDateEnd($date)
            ->shouldBeCalled()
            ->willReturn($this->timer->reveal());

        $this->time->stopTimer(
            $this->user->reveal(),
            $this->timer->reveal()
        );
    }

    public function testStopNonPunchTimers()
    {
        $this->timeEntryRepository->findNonPunchTimer($this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($this->timer->reveal());

        $date = new \DateTime('now');
        $this->dateTimeProvider->getLocalUserTime($this->user->reveal())
                               ->shouldBeCalled()
                               ->willReturn($date);

        $this->timer->setDateEnd($date)
                    ->shouldBeCalled()
                    ->willReturn($this->timer->reveal());

        $this->time->stopNonPunchTimers(
            $this->user->reveal()
        );
    }

    /** @dataProvider validTimesProvider */
    public function testStartTimerFromValidTimeString($timeString)
    {
        preg_match('/^([01]?\d|2[0-3]):?([0-5]\d)/', $timeString, $militaryTime);
        $date = \DateTime::createFromFormat('Y-m-d H:i', date('Y-m-d').' '.$militaryTime[1].':'.$militaryTime[2]);
        $this->dateTimeProvider->getLocalUserTime($this->user->reveal())
                               ->shouldBeCalled()
                               ->willReturn($date);

        $this->timerFactory->createTimerObject(TimerType::PUNCH, $this->user->reveal(), $date)
                           ->shouldBeCalled()
                           ->willReturn($this->timer->reveal());

        $this->time->startTimerFromTimeString($this->user->reveal(), $timeString, TimerType::PUNCH);
    }

    /** @
     * @dataProvider invalidTimesProvider
     */
    public function testStartTimerFromInvalidTimeString($timeString)
    {
        $this->expectException(MessageHandlerException::class);

        $this->time->startTimerFromTimeString($this->user->reveal(), $timeString, TimerType::PUNCH);
    }

    /**
     * @dataProvider validBreakTimesProvider
     */
    public function testAddFinishedTimerValidForm($timeString)
    {
        $userLocalTime = new \DateTime('2019-09-09 01:00:00');
        $this->dateTimeProvider->getLocalUserTime($this->user->reveal())
                               ->shouldBeCalled()
                               ->willReturn($userLocalTime);

        $dateEnd = clone($userLocalTime);
        $timeParts = explode(':', $timeString);
        $dateEnd->add(new \DateInterval(sprintf('PT%sH%sM', $timeParts[0], $timeParts[1])));

        $this->timerFactory->createTimerObject(TimerType::WORK, $this->user->reveal(), $userLocalTime ,$dateEnd);
        $this->time->addFinishedTimer($this->user->reveal(), TimerType::WORK, $timeString);
    }

    /**
     * @dataProvider invalidBreakTimesProvider
     */
    public function testAddFinishedTimerInvalidForm($timeString)
    {
        $this->expectException(MessageHandlerException::class);
        $this->time->addFinishedTimer($this->user->reveal(), TimerType::WORK, $timeString);
    }

    public function validTimesProvider()
    {
        return [
                ['13:30'],
                ['1:30'],
                ['07:35 PM'],
                ['11:35 am'],
                ['0834'],
                ['23:33']
        ];
    }

    public function invalidTimesProvider()
    {
        return [
            ['13'],
            ['25:30'],
            ['2x:30'],
            ['24:30'],
            ['11:77'],
            ['1a:77'],
            ['33:33'],
            ['0 sm'],
        ];
    }

    public function validBreakTimesProvider()
    {
        return [
                ['13:30'],
                ['1:30'],
                ['07:35'],
                ['11:35'],
                ['08:34'],
                ['23:33'],
        ];
    }

    public function invalidBreakTimesProvider()
    {
        return [
                ['130'],
                ['24:30'],
                ['11:77'],
                ['1a:77'],
                ['33:33'],
        ];
    }
}
