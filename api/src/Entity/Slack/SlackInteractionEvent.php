<?php

namespace App\Entity\Slack;

use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource(
 *     messenger=true,
 *     collectionOperations={
 *         "post"={
 *              "method"="POST",
 *              "path"="/slack/event/interaction",
 *              "status"=202
 *          },
 *     },
 *     itemOperations={},
 *     output=false
 * )
 */
class SlackInteractionEvent
{
    const ENDPOINT_PATH = '/slack/event/interaction';

    public $payload;

    public function getPayload()
    {
        return json_decode($this->payload, true);
    }

    public function getEventType()
    {
        if (is_array($this->payload)) {
            return $this->payload['type'];
        }

        return json_decode($this->payload, true)['type'];
    }
}
