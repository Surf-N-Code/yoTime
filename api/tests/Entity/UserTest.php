<?php

namespace App\Tests\Entity;

use App\Entity\Timer;
use App\Entity\TimerType;
use App\Entity\User;
use App\Tests\IntegrationTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class UserTest extends IntegrationTestCase
{
    use ReloadDatabaseTrait;

    public function testCreateUser()
    {
        $client = $this->createAuthenticatedClient();

        $response = $client->request(
            'POST',
            '/users',
            [
                'json' => [
                    'email'    => 'test@test.de',
                    'password' => 'trustno1',
                    'tzOffset' => -60,
                    'timezone' => 'Europe/Amsterdam',
                    'fullName' => 'norman dilthey'
                ],
                'base_uri' => 'https://localhost:8443'
            ]
        );

        self::assertEquals(201, $response->getStatusCode());
    }
}
