<?php


namespace App\Handler\MessageHandler\Slack;


use App\Entity\Slack\SlackMessage;use App\Entity\Slack\SlashCommand;
use App\Exceptions\MessageHandlerException;
use App\Mail\Mailer;
use App\Security\ResetPasswordHandler;
use App\Services\UserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterHandler
{

    private UserProvider $userProvider;

    private ResetPasswordHandler $resetPasswordHandler;

    public function __construct(
        UserProvider $userProvider,
        ResetPasswordHandler $resetPasswordHandler
    )
    {
        $this->userProvider = $userProvider;
        $this->resetPasswordHandler = $resetPasswordHandler;
    }

    public function register(SlashCommand $command)
    {
        $m = new SlackMessage();
        $slackUser = $this->userProvider->getSlackUser($command->getUserId());
        $user = $this->userProvider->populateUserEntityFromSlackInfo($slackUser);
        $user->setIsActive(true);
        try {
            $this->resetPasswordHandler->resetUserPassword($user);
        } catch (\Exception $e) {
            return $m->addTextSection('Ups, could not send your registration mail. Please contact support to receive your login password.');
        }

        return $m->addTextSection('Registered successfully :hugging_face:');
    }
}
