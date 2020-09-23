<?php

namespace App\Tests\Entity;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\TestCase;

class UserTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public function testGetUsers()
    {
        $response = static::createClient()->request(
            'GET',
            '/users',
        );
        self::assertEquals(200, $response->getStatusCode());
    }
}
