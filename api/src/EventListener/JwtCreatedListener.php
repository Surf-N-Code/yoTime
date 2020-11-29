<?php


namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class JwtCreatedListener
{

    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack, Security $security)
    {
        $this->security = $security;
        $this->requestStack = $requestStack;
    }

    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        /** @var \App\Entity\User $user */
        $user = $this->security->getUser();
        $payload       = $event->getData();
        $first = substr($user->getFirstName(), 0, 1) ?? '';
        $last = substr($user->getLastName(), 0, 1) ?? '';

        $payload['initials'] = $first.$last;
        $payload['ip'] = $request->getClientIp();

        $event->setData($payload);
    }
}
