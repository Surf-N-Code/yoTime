<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource(
 *     messenger=true,
 *     collectionOperations={
 *         "post"={
 *              "method"="POST",
 *              "path"="/verify-token",
 *              "status"=202
 *          },
 *     },
 *     itemOperations={},
 *     output=false
 * )
 */
class JwtToken
{
}
