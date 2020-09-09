<?php

namespace App\Entity\Slack;

use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource(
 *     messenger=true,
 *     collectionOperations={
 *         "post"={
 *              "method"="POST",
 *              "path"="/slack/interaction",
 *              "status"=202
 *          },
 *     },
 *     itemOperations={},
 *     output=false
 * )
 */
class SlackInteractionEvent
{
    const ENDPOINT_PATH = '/slack/interaction';

    public $payload;

    public function getPayload()
    {
        return json_decode($this->payload, true);
    }
}
