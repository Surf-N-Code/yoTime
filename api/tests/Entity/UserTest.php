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

        $em = self::$container->get('doctrine')->getManager();

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

        $user = $em->getRepository(User::class)->findBy(['email' => 'test@test.de']);
        dd($user);
        self::assertStringContainsString('$argon2i$v=19$m=65536,t=4,p=1$', $user[0]->getPassword());
        self::assertEquals(201, $response->getStatusCode());
    }
}
