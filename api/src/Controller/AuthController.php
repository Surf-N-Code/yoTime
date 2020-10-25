<?php

namespace App\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthController extends AbstractController
{
    public function register(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $em = $this->getDoctrine()->getManager();

        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $tz = $request->request->get('timezone');
        $tzOffset = $request->request->get('timezoneOffset');

        $user = new User();
        $user->setUsername($username);
        $user->setIsActive(1);
        $user->setTz($tz);
        $user->setTzOffset($tzOffset);
        $user->setPassword($encoder->encodePasswrouteord($user, $password));

        try {
            $em->persist($user);
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            return new Response('Duplicate Email', Response::HTTP_CONFLICT);
        }

        return new Response(sprintf('User %s successfully created', $user->getUsername()));
    }

    public function validateToken(Request $request)
    {
        $params = json_decode($request->getContent(), true);
        if (isset($params['authToken']) && !empty($params['authToken'])) {
            return true;
        }
        return false;
    }

    public function api()
    {
        return new Response(sprintf('Logged in as %s', $this->getUser()->getUsername()));
    }
}
