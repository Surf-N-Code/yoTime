<?php


namespace App\EventListener;


use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\Cookie;

class AuthenticationSuccessListener
{
//    private $jwtTokenTTL;
//
//    private $cookieSecure = false;
//
//    public function __construct($ttl)
//    {
//        $this->jwtTokenTTL = $ttl;
//    }
//
//    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
//    {
//        $response = $event->getResponse();
//        $data = $event->getData();
//        $tokenJWT = $data['token'];
//
//        $response->headers->setCookie(new Cookie('BEARER', $tokenJWT, (
//        new \DateTime())
//            ->add(new \DateInterval('PT' . $this->jwtTokenTTL . 'S')), '/', null, $this->cookieSecure));
//
//        return $response;
//    }
}
