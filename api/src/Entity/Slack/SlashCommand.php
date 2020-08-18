<?php

namespace App\Entity\Slack;

use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource(
 *     messenger=true,
 *     collectionOperations={
 *         "post"={
 *              "method"="POST",
 *              "path"="/slack/slashcommand",
 *          },
 *     },
 *     itemOperations={},
 *     output=false
 * )
 */
class SlashCommand extends AbstractSlack
{
}
