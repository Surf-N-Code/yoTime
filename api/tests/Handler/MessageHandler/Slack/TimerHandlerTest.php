<?php


namespace App\Tests\Handler\MessageHandler\Slack;


use App\Entity\Slack\SlashCommand;
use App\Entity\TimeEntry;
use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Handler\MessageHandler\Slack\PunchTimerHandler;use App\Handler\MessageHandler\Slack\TimerHandler;
use App\Repository\TimeEntryRepository;
use App\Services\Time;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class TimerHandlerTest extends TestCase
{
    use ProphecyTrait;

    private $timeEntryRepository;
    private $time;
    private $user;
    private $dateTimeProvider;
    private $timeEntryProphecy;
    private $punchTimerHandler;

    private TimerHandler $timerHandler;
    
    public function setup(): void
    {
        $this->timeEntryRepository = $this->prophesize(TimeEntryRepository::class);
        $this->user = $this->prophesize(User::class);
        $this->time = $this->prophesize(Time::class);
        $this->timeEntryProphecy = $this->prophesize(TimeEntry::class);
        $this->punchTimerHandler = $this->prophesize(PunchTimerHandler::class);

        $this->timerHandler = new TimerHandler(
            $this->time->reveal(),
            $this->timeEntryRepository->reveal(),
            $this->punchTimerHandler->reveal()
        );
    }
    
    private function generateSlashcommand($command, $text)
    {
        $s = new SlashCommand();
        $s->setTeamId("Team1");
        $s->setChannelId("ChannelID1");
        $s->setChannelName("Channel1");
        $s->setCommand($command);
        $s->setText($text);
        $s->setTriggerId('123');
        $s->setUserId('user_123');
        $s->setUserName('Norman');
        return $s;
    }

    public function testStartWorkTimer()
    {
        $parameters = [
            'slashcommand' => '/work',
            'timerType'    => TimerType::WORK
        ];

        $command = $this->generateSlashcommand($parameters['slashcommand'], '');

        $this->time->stopNonPunchTimers($this->user->reveal())
                   ->shouldBeCalled();
        $this->time->startTimer($this->user->reveal(), $parameters['timerType'])
                   ->shouldBeCalled()
                   ->willReturn($this->timeEntryProphecy->reveal());
        $this->timerHandler->startTimer($command, $this->user->reveal());
    }

    public function testBreakTimerStart()
    {
        $parameters = [
            'slashcommand' => '/break',
            'timerType'    => TimerType::BREAK
        ];

        $command = $this->generateSlashcommand($parameters['slashcommand'], '');

        $this->time->stopNonPunchTimers($this->user->reveal())
                   ->shouldBeCalled();
        $this->time->startTimer($this->user->reveal(), $parameters['timerType'])
                   ->shouldBeCalled()
                   ->willReturn($this->timeEntryProphecy->reveal());
        $this->timerHandler->startTimer($command, $this->user->reveal());

    }

    public function testBreakTimerManually()
    {
        $parameters = [
            'slashcommand' => '/late_break',
            'commandText'  => '07:30',
        ];

        $command = $this->generateSlashcommand($parameters['slashcommand'], $parameters['commandText']);

        $this->time->getHoursAndMinutesFromString($parameters['commandText'])
                   ->shouldBeCalled()
                   ->willReturn(['07', '30']);
        $this->time->addFinishedTimer($this->user->reveal(), TimerType::BREAK, ['07', '30'])
                   ->shouldBeCalled()
                   ->willReturn($this->timeEntryProphecy->reveal());
        $this->timerHandler->addBreakManually($command, $this->user->reveal());
    }

    public function testBreakTimerMauallyInvalid()
    {
        $parameters = [
            'slashcommand' => '/late_break',
            'commandText'  => '0730',
        ];

        $command = $this->generateSlashcommand($parameters['slashcommand'], $parameters['commandText']);

         $this->time->getHoursAndMinutesFromString($parameters['commandText'])
                   ->shouldBeCalled()
                   ->willThrow(MessageHandlerException::class);
        $this->time->addFinishedTimer($this->user->reveal(), TimerType::BREAK, ['07', '30'])
                   ->shouldNotBeCalled();
        $this->expectException(MessageHandlerException::class);
        $this->timerHandler->addBreakManually($command, $this->user->reveal());
    }

    public function testStopTimer()
    {
        $parameters = [
            'slashcommand' => '/end_work',
            'timerType'    => TimerType::WORK
        ];

        $command = $this->generateSlashcommand($parameters['slashcommand'], '');

        $this->timeEntryRepository->findNonPunchTimers($this->user->reveal())
                    ->shouldBeCalled()
                    ->willReturn($this->timeEntryProphecy->reveal());
        $this->time->stopTimer($this->user->reveal(), $this->timeEntryProphecy->reveal())
                   ->shouldBeCalled()
                   ->willReturn($this->timeEntryProphecy->reveal());
        $this->timerHandler->stopTimer($this->user->reveal(), $command);
    }

    public function testStopTimerWithTask()
    {
        $parameters = [
            'slashcommand' => '/end_work',
            'timerType'    => TimerType::WORK
        ];

        $command = $this->generateSlashcommand($parameters['slashcommand'], 'Task description');

        $this->timeEntryRepository->findNonPunchTimers($this->user->reveal())
                    ->shouldBeCalled()
                    ->willReturn($this->timeEntryProphecy->reveal());
        $this->time->addTaskToTimeEntry($this->timeEntryProphecy->reveal(), 'Task description')
                    ->shouldBeCalled()
                    ->willReturn($this->timeEntryProphecy->reveal());
        $this->time->stopTimer($this->user->reveal(), $this->timeEntryProphecy->reveal())
                   ->shouldBeCalled()
                   ->willReturn($this->timeEntryProphecy->reveal());
        $this->timerHandler->stopTimer($this->user->reveal(), $command);
    }

    public function testStopTimerWithoutRunningTimer()
    {
        $parameters = [
            'slashcommand' => '/end_work',
            'timerType'    => TimerType::WORK
        ];

        $command = $this->generateSlashcommand($parameters['slashcommand'], '');

        $this->timeEntryRepository->findNonPunchTimers($this->user->reveal())
                    ->shouldBeCalled()
                    ->willReturn(null);
        $this->time->stopTimer($this->user->reveal(), $this->timeEntryProphecy->reveal())
                   ->shouldNotBeCalled()
                   ->willReturn($this->timeEntryProphecy->reveal());
        $this->expectException(MessageHandlerException::class);
        $this->timerHandler->stopTimer($this->user->reveal(), $command);
    }

    public function testLateSigninTime()
    {
        $this->timeEntryRepository->findPunchTimer($this->user->reveal())
                                  ->shouldBeCalled()
                                  ->willReturn(null);

        $this->punchTimerHandler->punchInAtTime($this->user->reveal(), '09:03')
            ->shouldBeCalled()
            ->willReturn($this->timeEntryProphecy->reveal());

        $this->timerHandler->lateSignIn($this->user->reveal(), '09:03');
    }

    public function testLateSignInWithPunchTimerRunning()
    {
        $parameters = [
            'slashcommand' => '/late_hi',
            'dateTime'     => new \DateTime(),
        ];

        $this->timeEntryRepository->findPunchTimer($this->user->reveal())
                                  ->shouldBeCalled()
                                  ->willReturn($this->timeEntryProphecy->reveal());

        $this->timeEntryProphecy->getDateStart()
            ->shouldBeCalled()
            ->willReturn($parameters['dateTime']);

        $this->expectException(MessageHandlerException::class);
        $this->timerHandler->lateSignIn($this->user->reveal(), '09:03');
    }


    public function testLateSigninInvalid()
    {
        $parameters = [
            'slashcommand' => '/late_hi',
            'timerType'    => TimerType::PUNCH
        ];

        $this->timeEntryRepository->findPunchTimer($this->user->reveal())
                    ->shouldBeCalled()
                    ->willReturn(null);

        $this->punchTimerHandler->punchInAtTime($this->user->reveal(), '25:99')
            ->shouldBeCalled()
            ->willThrow(MessageHandlerException::class);

        $this->expectException(MessageHandlerException::class);
        $this->timerHandler->lateSignIn($this->user->reveal(), '25:99');
    }


    public function invalidLateSigninDataProvider()
    {
        return [
            [
                'a',
                '24:30',
                '11:77',
                '1a:77',
                '33:33',
                '130',
            ],
        ];
    }
}
