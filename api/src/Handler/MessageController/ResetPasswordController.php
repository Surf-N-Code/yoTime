<?php


namespace App\Handler\MessageController;

use App\Entity\ResetPassword;
use App\Entity\User;
use App\Security\ResetPasswordHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ResetPasswordController implements MessageHandlerInterface
{
    private EntityManagerInterface $em;

    private ResetPasswordHandler $resetPasswordHandler;

    public function __construct(
        EntityManagerInterface $em,
        ResetPasswordHandler $resetPasswordHandler
    )
    {
        $this->em = $em;
        $this->resetPasswordHandler = $resetPasswordHandler;
    }

    public function __invoke(ResetPassword $resetPassword): Response
    {
        try {
            $user = $this->em->getRepository(User::class)->findBy(['email' => $resetPassword->getEmail()]);
            if (!empty($user)) {
                $userEntity = $user[0];
                $this->resetPasswordHandler->resetUserPassword($userEntity);
            }
        } catch (\Exception $e) {
            return new JsonResponse('Ups.. an error occurred.', Response::HTTP_BAD_REQUEST);
        }
        return new JsonResponse('Password reset email sent', Response::HTTP_OK);
    }
}
