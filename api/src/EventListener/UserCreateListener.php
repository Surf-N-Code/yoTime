<?php

namespace App\EventListener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use App\Exceptions\UniqueConstraintViolationException;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UserCreateListener implements EventSubscriberInterface
{
    const ALLOWED_OPERATIONS = ['post'];

    private $userRepo;

    private ValidatorInterface $validator;

    public function __construct(UserRepository $userRepo, ValidatorInterface $validator)
    {
        $this->userRepo = $userRepo;
        $this->validator = $validator;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [
                ['checkForDuplicateEntry', EventPriorities::PRE_VALIDATE],
            ]
        ];
    }

    public function checkForDuplicateEntry(ViewEvent $event)
    {
        $obj = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        $operationName = $event->getRequest()->attributes->get('_api_collection_operation_name');

        if (!$obj instanceof User || Request::METHOD_POST !== $method || !in_array(
                $operationName,
                self::ALLOWED_OPERATIONS,
                true
            )) {
            return;
        }

        $validationErrors = $this->validator->validate($obj);
        if ($validationErrors) {
            throw new UniqueConstraintViolationException(sprintf('User with email "%s" already exist.', $obj->getEmail()));
        }
    }
}
