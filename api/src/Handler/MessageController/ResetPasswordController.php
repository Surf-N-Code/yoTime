<?php


namespace App\Handler\MessageController;

use App\Entity\ResetPassword;
use App\Entity\Slack\SlackBotMessage;
use App\Entity\Slack\SlackMessage;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;use App\Handler\MessageHandler\Slack\BotMessageHandler;
use App\Security\ResetPasswordHandler;
use App\Services\JsonBodyTransform;
use App\Services\UserProvider;
use App\Slack\SlackClient;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class ResetPasswordController implements MessageHandlerInterface
{
    private LoggerInterface $logger;

    private EntityManagerInterface $em;

    private UserProvider $userProvider;

    private UserPasswordEncoderInterface $userPasswordEncoder;

    private ResetPasswordHandler $resetPasswordHandler;

    public function __construct(
        EntityManagerInterface $em,
        UserProvider $userProvider,
        UserPasswordEncoderInterface $userPasswordEncoder,
        ResetPasswordHandler $resetPasswordHandler,
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->userProvider = $userProvider;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->resetPasswordHandler = $resetPasswordHandler;
    }

    public function __invoke(ResetPassword $resetPassword): Response
    {
        try {
            $user = $this->em->getRepository(User::class)->findBy(['email' => $resetPassword->getEmail()]);
            if (!empty($user)) {
                //generate temp password and save in user entity
                $userEntity = $user[0];
                $this->resetPasswordHandler->resetUserPassword($userEntity);
                $randomPass = $this->userProvider->randomPassword();
                $userEntity->setPassword($this->userPasswordEncoder->encodePassword($userEntity, $randomPass));
            }
        } catch (\Exception $e) {
            dd($e);
            return new Response('Ups.. an error occurred.', Response::HTTP_BAD_REQUEST);
        }
        return new Response('Passwrod reset email sent', Response::HTTP_OK);
    }
}
