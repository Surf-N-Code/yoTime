<?php

namespace App\Entity\Slack;

use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource(
 *     messenger=true,
 *     collectionOperations={
 *         "post"={
 *              "method"="POST",
 *              "path"="/slack/event/bot",
 *              "status"=202
 *          },
 *     },
 *     itemOperations={},
 *     output=false
 * )
 */
class SlackBotMessage extends AbstractSlack
{
    const ENDPOINT_PATH = '/slack/event/bot';

    /**
     * @var SlackBotEvent
     */
    private $event;

    /**
     * @return SlackBotEvent
     */
    public function getEvent(): SlackBotEvent
    {
        return $this->event;
    }

    /**
     * @param SlackBotEvent $event
     */
    public function setEvent(SlackBotEvent $event): void
    {
        $this->event = $event;
    }
}
