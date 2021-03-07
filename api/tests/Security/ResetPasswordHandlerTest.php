<?php


namespace App\Tests\Security;


use App\Entity\User;
use App\Mail\Mailer;
use App\Security\ResetPasswordHandler;
use App\Services\UserProvider;
use App\Tests\IntegrationTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordHandlerTest extends IntegrationTestCase
{

    use ProphecyTrait;

    private $em;
    private $user;
    private $userProvider;
    private ResetPasswordHandler $resetPasswordHandler;
    private $userPasswordEncoder;
    private $mailer;

    public function setup(): void
    {
        $this->userProvider = $this->prophesize(UserProvider::class);
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->mailer = $this->prophesize(Mailer::class);
        $this->userPasswordEncoder = $this->prophesize(UserPasswordEncoderInterface::class);
        $this->user = $this->prophesize(User::class);

        $this->resetPasswordHandler = new ResetPasswordHandler(
            $this->userProvider->reveal(),
            $this->userPasswordEncoder->reveal(),
            $this->em->reveal(),
            $this->mailer->reveal(),
        );
    }

    public function testResetPassword()
    {

        $this->user->setPassword('encodedPassword')
                   ->shouldBeCalled();
        $this->user->getEmail()
                   ->shouldBeCalled()
                   ->willReturn('ndilthey@gmail.com');

        $this->userProvider->randomPassword()
                           ->shouldBeCalled()
                           ->willReturn('randompass');

        $this->userPasswordEncoder->encodePassword($this->user->reveal(), 'randompass')
                                  ->shouldBeCalled()
                                  ->willReturn('encodedPassword');

        $this->em->persist($this->user->reveal())
                 ->shouldBeCalled();

        $this->em->flush()
                 ->shouldBeCalled();

        $mailContent = 'Hi there<br>here is your temporary password for YoTime: randompass<br>Follow this link to login and change your password:<br>https://localhost:8443<br>';
        $this->mailer->send(Argument::type('string'), Argument::type('string'), Argument::type('string'), $mailContent)
                     ->shouldBeCalled();

        $this->resetPasswordHandler->resetUserPassword($this->user->reveal());
    }
}
