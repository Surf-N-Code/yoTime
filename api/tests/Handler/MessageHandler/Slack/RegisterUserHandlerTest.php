<?php

namespace App\Tests\Handler\MessageHandler\Slack;

use App\Entity\Slack\SlackUser;
use App\Entity\Slack\SlashCommand;
use App\Entity\User;
use App\Handler\MessageHandler\Slack\RegisterHandler;
use App\Security\ResetPasswordHandler;
use App\Services\UserProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;

class RegisterUserHandlerTest extends TestCase
{
    use ProphecyTrait;

    private $em;
    private $user;
    private $userProvider;
    private RegisterHandler $registerHandler;
    private $resetPasswordHandler;

    public function setup(): void
    {
        $this->userProvider = $this->prophesize(UserProvider::class);
        $this->resetPasswordHandler = $this->prophesize(ResetPasswordHandler::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->user = $this->prophesize(User::class);

        $this->registerHandler = new RegisterHandler(
            $this->userProvider->reveal(),
            $this->resetPasswordHandler->reveal(),
            $this->logger->reveal()
        );
    }

    public function testRegister()
    {
        $sc = new SlashCommand();
        $sc->setUserId('user-123');
        $slackUser = new SlackUser();
        $slackUser->setId('user-123');

        $this->userProvider->getSlackUser('user-123')
            ->shouldBeCalled()
            ->willReturn($slackUser);
        $this->userProvider->populateUserEntityFromSlackInfo($slackUser)
            ->shouldBeCalled()
            ->willReturn($this->user->reveal());

        $this->user->setIsActive(true)
            ->shouldBeCalled();

        $this->resetPasswordHandler->resetUserPassword($this->user->reveal())
            ->shouldBeCalled();

        $this->registerHandler->register($sc);
    }
}
