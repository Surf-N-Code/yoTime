<?php

namespace App\Tests\Handler\MessageHandler\Slack;

use App\Entity\Slack\PunchTimerStatusDto;
use App\Entity\Slack\SlackMessage;
use App\Entity\Timer;
use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Handler\MessageHandler\Slack\BotMessageHandler;
use App\Handler\MessageHandler\Slack\TimerHandler;
use App\Repository\TimerRepository;
use App\Repository\UserRepository;
use App\Services\DatabaseHelper;
use App\Services\Time;
use App\Services\UserProvider;
use App\Slack\SlackClient;
use App\Slack\SlackMessageHelper;
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
    private $timerHandler;
    private $slackMessage;
    private $slackMessageHelper;
    private $timer;
    private $databaseHelper;
    private $slackClient;

    protected function setUp(): void
    {
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->userRepository = $this->prophesize(UserRepository::class);
        $this->timeEntryRepository = $this->prophesize(TimerRepository::class);
        $this->timerType = $this->prophesize(TimerType::class);
        $this->timer = $this->prophesize(Timer::class);
        $this->userProvider = $this->prophesize(UserProvider::class);
        $this->user = $this->prophesize(User::class);
        $this->time = $this->prophesize(Time::class);
        $this->userRepository = $this->prophesize(UserRepository::class);
        $this->timerHandler = $this->prophesize(TimerHandler::class);
        $this->slackMessage = $this->prophesize(SlackMessage::class);
        $this->slackMessageHelper = $this->prophesize(SlackMessageHelper::class);
        $this->slackClient = $this->prophesize(SlackClient::class);
        $this->databaseHelper = $this->prophesize(DatabaseHelper::class);
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
            $this->timerHandler->reveal(),
            $this->time->reveal(),
            $this->slackClient->reveal(),
            $this->databaseHelper->reveal()
        );
        $this->expectException(MessageHandlerException::class);
        $botMessageHandler->parseEventType($this->buildEvent('app_mention', '/hey'));
    }

    public function testUnsupportedEventType()
    {
        $botMessageHandler = new BotMessageHandler(
            $this->userProvider->reveal(),
            $this->timerHandler->reveal(),
            $this->time->reveal(),
            $this->slackClient->reveal(),
            $this->databaseHelper->reveal()
        );
        $this->expectException(MessageHandlerException::class);
        $botMessageHandler->parseEventType($this->buildEvent('unsupported_event', '/hey'));
    }

    public function testHiEvent()
    {
        $user = $this->buildUser();
        $this->userProvider->getDbUserBySlackId($user->getSlackUserId())
                           ->shouldBeCalled()
                           ->willReturn($user);
        $this->timerHandler->startTimer($user, TimerType::WORK)
            ->shouldBeCalled()
            ->willReturn($this->timer->reveal());

        $botMessageHandler = new BotMessageHandler(
            $this->userProvider->reveal(),
            $this->timerHandler->reveal(),
            $this->time->reveal(),
            $this->slackClient->reveal(),
            $this->databaseHelper->reveal()
        );
        $botMessageHandler->parseEventType($this->buildEvent('app_mention', '/hey'));
    }

    public function testByeEvent()
    {
        $user = $this->buildUser();
        $this->userProvider->getDbUserBySlackId($user->getSlackUserId())
                           ->shouldBeCalled()
                           ->willReturn($user);
        $this->timerHandler->punchOut($user)
                                ->shouldBeCalled()
                                ->willReturn(new PunchTimerStatusDto(true, $this->timer->reveal()));

        $this->time->getTimesSpentByTypeAndPeriod($user, 'day')
            ->shouldBeCalled()
            ->willReturn([3600, 600]);

        $this->time->formatSecondsAsHoursAndMinutes(3000)
            ->shouldBeCalled()
            ->willReturn('0h 20min');

        $this->time->formatSecondsAsHoursAndMinutes(600)
                   ->shouldBeCalled()
                   ->willReturn('0h 10min');

        $botMessageHandler = new BotMessageHandler(
            $this->userProvider->reveal(),
            $this->timerHandler->reveal(),
            $this->time->reveal(),
            $this->slackClient->reveal(),
            $this->databaseHelper->reveal()
        );
        $botMessageHandler->parseEventType($this->buildEvent('app_mention', '/bye'));
    }

    public function testByeEventNotPunchedIn()
    {
        $user = $this->buildUser();
        $this->userProvider->getDbUserBySlackId($user->getSlackUserId())
                           ->shouldBeCalled()
                           ->willReturn($user);
        $this->timerHandler->punchOut($user)
                                ->shouldBeCalled()
                                ->willThrow(MessageHandlerException::class);

        $botMessageHandler = new BotMessageHandler(
            $this->userProvider->reveal(),
            $this->timerHandler->reveal(),
            $this->time->reveal(),
            $this->slackClient->reveal(),
            $this->databaseHelper->reveal()
        );
        $this->expectException(MessageHandlerException::class);
        $botMessageHandler->parseEventType($this->buildEvent('app_mention', '/bye'));
    }

    public function testByeEventAlreadyPunchedOut()
    {
        $user = $this->buildUser();
        $this->userProvider->getDbUserBySlackId($user->getSlackUserId())
                           ->shouldBeCalled()
                           ->willReturn($user);
        $this->timerHandler->punchOut($user)
                                ->shouldBeCalled()
                                ->willReturn(new PunchTimerStatusDto(false, $this->timer->reveal()));

        $botMessageHandler = new BotMessageHandler(
            $this->userProvider->reveal(),
            $this->timerHandler->reveal(),
            $this->time->reveal(),
            $this->slackClient->reveal(),
            $this->databaseHelper->reveal()
        );
        $botMessageHandler->parseEventType($this->buildEvent('app_mention', '/bye'));
    }

    public function testHiEventAlreadyPunchedIn()
    {
        $user = $this->buildUser();
        $this->userProvider->getDbUserBySlackId($user->getSlackUserId())
                           ->shouldBeCalled()
                           ->willReturn($user);
        $this->timerHandler->startTimer($user, TimerType::WORK)
                                ->shouldBeCalled()
                                ->willThrow(MessageHandlerException::class);

        $botMessageHandler = new BotMessageHandler(
            $this->userProvider->reveal(),
            $this->timerHandler->reveal(),
            $this->time->reveal(),
            $this->slackClient->reveal(),
            $this->databaseHelper->reveal()
        );
        $this->expectException(MessageHandlerException::class);
        $botMessageHandler->parseEventType($this->buildEvent('app_mention', '/hey'));
    }

    public function testUnsupportedEvent()
    {
        $user = $this->buildUser();
        $this->userProvider->getDbUserBySlackId($user->getSlackUserId())
                           ->shouldBeCalled()
                           ->willReturn($user);
        $this->timerHandler->punchOut($user)
            ->shouldNotBeCalled()
            ->willReturn($this->slackMessage->reveal());

        $this->timerHandler->startTimer($user, TimerType::WORK)
            ->shouldNotBeCalled();

        $botMessageHandler = new BotMessageHandler(
            $this->userProvider->reveal(),
            $this->timerHandler->reveal(),
            $this->time->reveal(),
            $this->slackClient->reveal(),
            $this->databaseHelper->reveal()
        );
        $this->expectException(MessageHandlerException::class);
        $botMessageHandler->parseEventType($this->buildEvent('app_mention', '/unsupported'));
    }
}
