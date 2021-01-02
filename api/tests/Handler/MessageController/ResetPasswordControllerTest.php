<?php

namespace App\Tests\Handler\MessageController;

use App\Tests\IntegrationTestCase;

class ResetPasswordControllerTest extends IntegrationTestCase
{
    public function testResetPassword()
    {
        $client = $this->createAuthenticatedClient();

        $response = $client->request(
            'POST',
            '/reset-password',
            [
                'json' => [
                    'email'    => 'test@test.de',
                ],
                'base_uri' => 'https://localhost:8443'
            ]
        );

        self::assertEquals(200, $response->getStatusCode());
    }
}
