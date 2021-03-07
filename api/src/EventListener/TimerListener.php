<?php


namespace App\EventListener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Timer;
use App\Entity\TimerType;
use App\Slack\SlackUserClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class TimerListener implements EventSubscriberInterface
{

    private SlackUserClient $slackUserClient;

    public function __construct(SlackUserClient $slackUserClient)
    {
        $this->slackUserClient = $slackUserClient;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [
                'startTimer', EventPriorities::POST_WRITE,
            ],
        ];
    }

    public function startTimer(ViewEvent $event): void
    {
        $timer = $event->getControllerResult();

        if (!$timer instanceof Timer &&
            (Request::METHOD_POST !== 'PATCH' ||
            Request::METHOD_POST !== $event->getRequest()->getMethod()))
        {
            return;
        }

        $statusText = '';
        $emoji = '';
        if ($timer->getTimerType() === TimerType::BREAK) {
            $statusText = 'Shortly away';
            $emoji = ':away:';
        }

        $this->slackUserClient->slackApiCall(
            'POST',
            'users.profile.set',
            [
                'profile' => [
                    'status_text' => $statusText,
                    'status_emoji' => $emoji,
                    'status_expiration' => 0
                ]
            ]
        );
    }
}
