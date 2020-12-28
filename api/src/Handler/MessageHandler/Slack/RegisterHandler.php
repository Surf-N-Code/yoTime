<?php


namespace App\Handler\MessageHandler\Slack;


use App\Entity\Slack\SlackMessage;use App\Entity\Slack\SlashCommand;
use App\Exceptions\MessageHandlerException;
use App\Mail\Mailer;
use App\Services\UserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterHandler
{

    private UserProvider $userProvider;

    private Mailer $mailer;

    private EntityManagerInterface $em;

    private UserPasswordEncoderInterface $userPasswordEncoder;

    public function __construct(
        UserProvider $userProvider,
        Mailer $mailer,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $userPasswordEncoder
    )
    {
        $this->userProvider = $userProvider;
        $this->mailer = $mailer;
        $this->em = $em;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    public function register(SlashCommand $command)
    {
        $m = new SlackMessage();
        $slackUser = $this->userProvider->getSlackUser($command->getUserId());
        $user = $this->userProvider->populateUserEntityFromSlackInfo($slackUser);
        $user->setIsActive(true);
        $randomPass = $this->userProvider->randomPassword();
        $user->setPassword($this->userPasswordEncoder->encodePassword($user, $randomPass));
        $this->em->persist($user);
        $this->em->flush();

        $mailContent = 'Hi there,\n\n' .
                        'here is your temporary password for YoTime: '.$randomPass.'\n\n' .
                        'Follow this link to login and change your password:\n' .
                        $_ENV['API_BASE_URL'] . '/login\n';

        try {
            $this->mailer->send(
                'ndilthey@gmail.com',
                'ndilthey@gmail.com',
                'YoTime Account Temporary Password',
                $mailContent
            );
        } catch (MessageHandlerException $e) {
            return $m->addTextSection('Ups, could not send your registration mail. Please contact support to receive your login password.');
        }

        return $m->addTextSection('Registered successfully :hugging_face:');
    }
}
