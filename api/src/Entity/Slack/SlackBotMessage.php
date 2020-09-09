<?php

namespace App\Entity\Slack;

use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "post"={
 *              "method"="POST",
 *              "path"="/slack/bot/message",
 *          },
 *     },
 *     itemOperations={},
 *     output=false
 * )
 */
class SlackBotMessage extends AbstractSlack
{
    const ENDPOINT_PATH = '/slack/bot/message';

    /**
     * @var SlackBotEvent
     */
    private $event;

    /**
     * @return \App\Entity\Slack\SlackBotEvent
     */
    public function getEvent(): \App\Entity\Slack\SlackBotEvent
    {
        return $this->event;
    }

    /**
     * @param \App\Entity\Slack\SlackBotEvent $event
     */
    public function setEvent(\App\Entity\Slack\SlackBotEvent $event): void
    {
        $this->event = $event;
    }
}
