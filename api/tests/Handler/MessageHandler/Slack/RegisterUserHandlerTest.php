<?php

namespace App\Tests\Handler\MessageHandler\Slack;

use App\Entity\Slack\SlackMessage;
use App\Entity\Slack\SlackUser;
use App\Entity\Slack\SlashCommand;
use App\Entity\Timer;
use App\Entity\TimerType;
use App\Entity\User;
use App\Handler\MessageHandler\Slack\DailySummaryHandler;
use App\Handler\MessageHandler\Slack\RegisterHandler;
use App\Handler\MessageHandler\Slack\SlashCommandHandler;
use App\Handler\MessageHandler\Slack\TimerHandler;
use App\Handler\MessageHandler\Slack\UserHelpHandler;
use App\Mail\Mailer;
use App\Services\DatabaseHelper;
use App\Services\Time;
use App\Services\UserProvider;
use App\Slack\SlackClient;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterUserHandlerTest extends TestCase
{
    use ProphecyTrait;

    private $em;
    private $user;
    private $mailer;
    private $userPasswordEncoder;
    private $userProvider;
    private RegisterHandler $registerHandler;
    private $sc;

    public function setup(): void
    {
        $this->userProvider = $this->prophesize(UserProvider::class);
        $this->mailer = $this->prophesize(Mailer::class);
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->userPasswordEncoder = $this->prophesize(UserPasswordEncoderInterface::class);
        $this->user = $this->prophesize(User::class);

        $this->registerHandler = new RegisterHandler(
            $this->userProvider->reveal(),
            $this->mailer->reveal(),
            $this->em->reveal(),
            $this->userPasswordEncoder->reveal()
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

        $this->user->setPassword(Argument::type('string'))
            ->shouldBeCalled();

        $this->userProvider->randomPassword()
            ->shouldBeCalled()
            ->willReturn('randompass');

        $this->userPasswordEncoder->encodePassword($this->user->reveal(), 'randompass')
            ->shouldBeCalled();

        $this->em->persist($this->user->reveal())
            ->shouldBeCalled();

        $this->em->flush()
            ->shouldBeCalled();

        $mailContent = 'Hi there,\n\nhere is your temporary password for YoTime: randompass\n\nFollow this link to login and change your password:\nhttps://localhost:8443/login\n';
        $this->mailer->send(Argument::type('string'), Argument::type('string'), Argument::type('string'), $mailContent)
            ->shouldBeCalled();

        $this->registerHandler->register($sc);
    }
}
