<?php


namespace App\Handler\MessageHandler\Slack;


use App\Entity\Slack\SlackMessage;use App\Entity\Slack\SlashCommand;
use App\Exceptions\MessageHandlerException;
use App\Mail\Mailer;
use App\Security\ResetPasswordHandler;
use App\Services\UserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterHandler
{

    private UserProvider $userProvider;

    private ResetPasswordHandler $resetPasswordHandler;

    private LoggerInterface $logger;

    public function __construct(
        UserProvider $userProvider,
        ResetPasswordHandler $resetPasswordHandler,
        LoggerInterface $logger
    )
    {
        $this->userProvider = $userProvider;
        $this->resetPasswordHandler = $resetPasswordHandler;
        $this->logger = $logger;
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
            $this->logger->error(sprintf('Error sending registration email to user: %s with message: %s', $user->getEmail(), $e->getMessage()));
            $message = 'Ups, could not send your registration mail. Please contact support to receive your login password.';
            if ($e->getCode()) {
                $message = sprintf('You have already registered with YoTime. Try: %s to get some hints on how to use the app.', SlashCommandHandler::HELP);
            }
            return $m->addTextSection($message);
        }

        $m->addTextSection(sprintf('Yo <@%s>! :call_me_hand: Welcome to YoTime :hugging_face:', $command->getUserId()));
        $m->addTextSection(sprintf('If you need some pointers on how to get started, try the `%s` command.', SlashCommandHandler::HELP));
        return $m;
    }
}
