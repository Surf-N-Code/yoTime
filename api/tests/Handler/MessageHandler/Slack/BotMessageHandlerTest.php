<?php

namespace App\Tests\Handler\MessageHandler\Slack;

use App\Entity\Slack\SlackMessage;use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Handler\MessageHandler\Slack\BotMessageHandler;
use App\Handler\MessageHandler\Slack\PunchTimerHandler;
use App\Repository\TimeEntryRepository;
use App\Repository\UserRepository;
use App\Services\Time;
use App\Services\UserProvider;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BotMessageHandlerTest extends TestCase
{

    use ProphecyTrait;

    private $userProvider;
    private $em;
    private $logger;
    private $userRepository;
    private $timeEntryRepository;
    private $time;
    private $timerType;
    private $user;
    private $punchTimerHandler;
    private $slackMessage;

    protected function setUp(): void
    {
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->userRepository = $this->prophesize(UserRepository::class);
        $this->timeEntryRepository = $this->prophesize(TimeEntryRepository::class);
        $this->timerType = $this->prophesize(TimerType::class);
        $this->userProvider = $this->prophesize(UserProvider::class);
        $this->user = $this->prophesize(User::class);
        $this->time = $this->prophesize(Time::class);
        $this->userRepository = $this->prophesize(UserRepository::class);
        $this->punchTimerHandler = $this->prophesize(PunchTimerHandler::class);
        $this->slackMessage = $this->prophesize(SlackMessage::class);
    }

    private function buildEvent($type, $text)
    {
        $evt['type'] = $type;
        $evt['text'] = $text;
        $evt['user'] = 'Norman';
        $evt['channel'] = 'Channel_123';
        return $evt;
    }

    private function buildUser(): User
    {
        $user = new User();
        $user->setSlackUserId('Norman');
        return $user;
    }

    public function testUnregisteredBotHiEvent()
    {
        $user = $this->buildUser();
        $this->userProvider->getDbUserBySlackId($user->getSlackUserId())
                           ->shouldBeCalled()
                           ->willThrow(NotFoundHttpException::class);

        $botMessageHandler = new BotMessageHandler(
            $this->userProvider->reveal(),
            $this->punchTimerHandler->reveal(),
            $this->time->reveal()
        );
        $this->expectException(MessageHandlerException::class);
        $botMessageHandler->parseEventType($this->buildEvent('app_mention', '/hi'));
    }

    public function testUnsupportedEventType()
    {
        $botMessageHandler = new BotMessageHandler(
            $this->userProvider->reveal(),
            $this->punchTimerHandler->reveal(),
            $this->time->reveal()
        );
        $this->expectException(MessageHandlerException::class);
        $botMessageHandler->parseEventType($this->buildEvent('unsupported_event', '/hi'));
    }

    public function testHiEvent()
    {
        $user = $this->buildUser();
        $this->userProvider->getDbUserBySlackId($user->getSlackUserId())
                           ->shouldBeCalled()
                           ->willReturn($user);
        $this->punchTimerHandler->punchIn($user)
            ->shouldBeCalled()
            ->willReturn($this->slackMessage->reveal());

        $botMessageHandler = new BotMessageHandler(
            $this->userProvider->reveal(),
            $this->punchTimerHandler->reveal(),
            $this->time->reveal()
        );
        $botMessageHandler->parseEventType($this->buildEvent('app_mention', '/hi'));
    }

    public function testByeEvent()
    {
        $user = $this->buildUser();
        $this->userProvider->getDbUserBySlackId($user->getSlackUserId())
                           ->shouldBeCalled()
                           ->willReturn($user);
        $this->punchTimerHandler->punchOut($user)
            ->shouldBeCalled()
            ->willReturn([60, 60, true]);

        $this->time->formatSecondsAsHoursAndMinutes(60)->shouldBeCalledTimes(2)->willReturn('1h 0min');

        $botMessageHandler = new BotMessageHandler(
            $this->userProvider->reveal(),
            $this->punchTimerHandler->reveal(),
            $this->time->reveal()
        );
        $botMessageHandler->parseEventType($this->buildEvent('app_mention', '/bye'));
    }

    public function testUnsupportedEvent()
    {
        $user = $this->buildUser();
        $this->userProvider->getDbUserBySlackId($user->getSlackUserId())
                           ->shouldBeCalled()
                           ->willReturn($user);
        $this->punchTimerHandler->punchOut($user)
            ->shouldNotBeCalled()
            ->willReturn($this->slackMessage->reveal());

        $this->punchTimerHandler->punchIn($user)
            ->shouldNotBeCalled()
            ->willReturn($this->slackMessage->reveal());

        $botMessageHandler = new BotMessageHandler(
            $this->userProvider->reveal(),
            $this->punchTimerHandler->reveal(),
            $this->time->reveal()
        );
        $this->expectException(MessageHandlerException::class);
        $botMessageHandler->parseEventType($this->buildEvent('app_mention', '/unsupported'));
    }
}