<?php


namespace App\Security;


use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Mail\Mailer;
use App\Services\UserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ResetPasswordHandler
{

    private UserProvider $userProvider;

    private UserPasswordEncoderInterface $userPasswordEncoder;

    private EntityManagerInterface $em;

    private Mailer $mailer;

    public function __construct(
        UserProvider $userProvider,
        UserPasswordEncoderInterface $userPasswordEncoder,
        EntityManagerInterface $em,
        Mailer $mailer
    )
    {
        $this->userProvider = $userProvider;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->em = $em;
        $this->mailer = $mailer;
    }

    public function resetUserPassword(User $user): void
    {
        $randomPass = $this->userProvider->randomPassword();
        $user->setPassword($this->userPasswordEncoder->encodePassword($user, $randomPass));
        $this->em->persist($user);
        $this->em->flush();

        $mailContent = 'Hi there<br>'.
                       'here is your temporary password for YoTime: '. $randomPass . '<br>' .
                       'Follow this link to login and change your password:<br>' .
                       $_ENV['API_BASE_URL'] . '<br>';

        $this->mailer->send(
            'norman@diltheymedia.com',
            $user->getEmail(),
            'YoTime Account Temporary Password',
            $mailContent
        );
    }
}
