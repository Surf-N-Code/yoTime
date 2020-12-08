<?php

namespace App\Tests\Entity;

use App\Entity\Timer;
use App\Entity\TimerType;
use App\Entity\User;
use App\Tests\IntegrationTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class DailySummaryTest extends IntegrationTestCase
{
    use ReloadDatabaseTrait;

    public function testCreateUser()
    {
        $client = $this->createAuthenticatedClient();

        $response = $client->request(
            'POST',
            '/daily_summaries',
            [
                'json' => [
                    'daily_summary' => 'Daily Summary Test',
                    'date' => '2020-10-31T11:09:38.069Z',
                    'time_worked_in_s' => 23988,
                    'time_break_in_s' => 238,
                    'is_email_sent' => true,
                    'is_synced_to_personio' => true,
                    'start_time' => '2020-10-31T11:09:38.069Z',
                    'end_time' => '2020-10-31T11:18:38.069Z',
                ],
                'base_uri' => 'https://localhost:8443'
            ]
        );

        self::assertEquals(201, $response->getStatusCode());
    }
}
