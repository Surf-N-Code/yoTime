<?php


namespace App\tests;


use App\Entity\Task;
use App\Entity\TimeEntry;
use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Repository\TimeEntryRepository;
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
    private $timeEntry;
    private $timeEntryRepository;
    private $timerType;
    private $dateTimeProvider;

    public function setup(): void
    {
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->userRepository = $this->prophesize(UserRepository::class);
        $this->timeEntryRepository = $this->prophesize(TimeEntryRepository::class);
        $this->task = $this->prophesize(Task::class);
        $this->timeEntry = $this->prophesize(TimeEntry::class);
        $this->user = $this->prophesize(User::class);
        $this->timerType = $this->prophesize(TimerType::class);
        $this->timeProphecy = $this->prophesize(Time::class);
        $this->dateTimeProvider = $this->prophesize(DateTimeProvider::class);

        $this->time = new Time(
            $this->em->reveal(),
            $this->logger->reveal(),
            $this->timeEntryRepository->reveal(),
            $this->userRepository->reveal(),
            $this->dateTimeProvider->reveal()
        );
    }

    public function testStartTimer()
    {
        $this->dateTimeProvider->getLocalUserTime($this->user->reveal())
            ->shouldBeCalled()
            ->willReturn((new \DateTime()));

        $this->em->flush()->shouldBeCalled();
        $this->em->persist(Argument::type(TimeEntry::class))->shouldBeCalled();

        $this->time->startTimer(
            $this->user->reveal(),
            $this->timerType->reveal(),
            (new \DateTime())
        );
    }

    public function validBreakTimesProvider()
    {
        return [
            [
                '13:30',
                '1:30',
                '07:35',
                '11:35',
                '08:34',
                '23:33',
            ]
        ];
    }

    /**
     * @dataProvider validBreakTimesProvider
     */
    public function testAddFinishedTimerValidForm($duration)
    {
        $userLocalTime = new \DateTime('2019-09-09 01:00:00');
        $this->dateTimeProvider->getLocalUserTime($this->user->reveal())
                               ->shouldBeCalled()
                               ->willReturn($userLocalTime);

//        $dateStart = (new \DateTime($userLocalTime->format('Y-m-d H:i:s')))->setTime(1,0,0);
//        $timeParts = explode(':', $duration);
//
//        $dateEnd = clone($dateStart);
//        $dateEnd = $dateEnd->add(new \DateInterval(sprintf('PT%sH%sM', $timeParts[0], $timeParts[1])));
//
//        $timeEntry = $this->prophesize(TimeEntry::class);
//        $timeEntry->setDateStart($dateStart)->shouldBeCalled();
//        $timeEntry->setDateEnd($dateEnd)->shouldBeCalled();

        $this->em->flush()->shouldBeCalled();
        $this->em->persist(Argument::type(TimeEntry::class))->shouldBeCalled();
        $this->time->addFinishedTimer($this->user->reveal(), TimerType::WORK, $duration);
    }

    public function invalidBreakTimesProvider()
    {
        return [
            [
                '130',
                '24:30',
                '11:77',
                '1a:77',
                '33:33',
            ]
        ];
    }

    /**
     * @dataProvider invalidBreakTimesProvider
     */
    public function testAddFinishedTimerInvalidForm($duration)
    {
        $this->expectException(MessageHandlerException::class);
        $this->dateTimeProvider->getLocalUserTime($this->user->reveal())
                               ->shouldNotBeCalled()
                               ->willReturn((new \DateTime()));

        $this->em->flush()->shouldNotBeCalled();
        $this->em->persist(Argument::type(TimeEntry::class))->shouldNotBeCalled();
        $this->time->addFinishedTimer($this->user->reveal(), TimerType::WORK, $duration);
    }

    public function testEndTimerWithDescription()
    {
        $this->dateTimeProvider->getLocalUserTime($this->user->reveal())
                               ->shouldBeCalled()
                               ->willReturn((new \DateTime()));

        $this->timeEntry->setTask(Argument::type(Task::class))->shouldBeCalled();
        $this->timeEntry->getDateStart()->shouldBeCalled()->willReturn(new \DateTime('now'));
        $this->timeEntry->setDateStart(Argument::type(\DateTime::class))->shouldBeCalled();
        $this->timeEntry->setDateEnd(Argument::type(\DateTime::class))->shouldBeCalled();
        $this->timeEntry->setUser($this->user->reveal())->shouldBeCalled();
        $this->timeEntry->setTimerType(TimerType::WORK)->shouldBeCalled();
        $this->timeEntry->getTimerType()->shouldBeCalled()->willReturn(TimerType::WORK);

        $this->em->flush()->shouldBeCalled();
        $this->em->persist(Argument::type(TimeEntry::class))->shouldBeCalled();
        $this->time->startTimer($this->user->reveal(), TimerType::WORK);

        $this->em->flush()->shouldBeCalled();
        $this->em->persist(Argument::type(TimeEntry::class))->shouldBeCalled();

        $this->time->stopTimer($this->user->reveal(), $this->timeEntry->reveal(), 'Description');
    }

    public function testEndTimerWithoutDescription()
    {
        $this->dateTimeProvider->getLocalUserTime($this->user->reveal())
                               ->shouldBeCalled()
                               ->willReturn((new \DateTime()));

        $this->timeEntry->setTask(Argument::type(Task::class))->shouldNotBeCalled();
        $this->timeEntry->getDateStart()->shouldBeCalled()->willReturn(new \DateTime('now'));
        $this->timeEntry->setDateStart(Argument::type(\DateTime::class))->shouldBeCalled();
        $this->timeEntry->setDateEnd(Argument::type(\DateTime::class))->shouldBeCalled();
        $this->timeEntry->setUser($this->user->reveal())->shouldBeCalled();
        $this->timeEntry->setTimerType(TimerType::WORK)->shouldBeCalled();
        $this->timeEntry->getTimerType()->shouldBeCalled()->willReturn(TimerType::WORK);


        $this->em->flush()->shouldBeCalled();
        $this->em->persist(Argument::type(TimeEntry::class))->shouldBeCalled();
        $this->time->startTimer($this->user->reveal(), TimerType::WORK);

        $this->em->flush()->shouldBeCalled();
        $this->em->persist(Argument::type(TimeEntry::class))->shouldBeCalled();

        $this->time->stopTimer($this->user->reveal(), $this->timeEntry->reveal(), null);
    }

    /** @dataProvider invalidTimesProvider */
    public function testLateSigninInvalidTimeformatProvided($invalidTime)
    {
        $this->dateTimeProvider->getUserLocalDateTime($this->user->reveal())->shouldNotBeCalled();
        $this->expectException(MessageHandlerException::class);
        $this->time->startTimerFromTimeString($this->user->reveal(), $invalidTime, TimerType::PUNCH);
    }

    public function invalidTimesProvider()
    {
        return [
            [
                '25:30' => '25:30',
                'a:30' => 'a:30',
                '0735 PM',
                '1335 PM',
                '1135 p.m.',
                '1135 p.m',
                '1175 p.m',
                '083',
                '83',
            ],
        ];
    }

    public function validTimesProvider()
    {
        return [
            [
                '13:30',
                '1:30',
                '07:35 PM',
                '11:35 am',
                '0834',
                '23:33'
            ]
        ];
    }

    /** @dataProvider validTimesProvider */
    public function testLateSigninValidTimeformatProvided($validTime)
    {
        $this->dateTimeProvider->getLocalUserTime($this->user->reveal())
                               ->shouldBeCalled()
                               ->willReturn((new \DateTime()));
        $this->em->persist(Argument::type(TimeEntry::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();
        $this->time->startTimerFromTimeString($this->user->reveal(), $validTime, TimerType::PUNCH);
    }
}
