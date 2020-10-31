<?php

namespace App\Tests\Doctrine;

use App\Entity\DailySummary;
use App\Entity\Task;
use App\Entity\Timer;
use App\Tests\IntegrationTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class SetUserListenerTest extends IntegrationTestCase
{
    use RefreshDatabaseTrait;

    public function testAddDailySummary()
    {
        $client = $this->createAuthenticatedClient();

        $data = [
            'daily_summary' => 'AutoUserSet',
            'date' => '2020-10-31T11:09:38.069Z',
            'time_worked_in_s' => 23988,
            'time_break_in_s' => 238,
            'is_email_sent' => true,
            'is_synced_to_personio' => true,
            'start_time' => '2020-10-31T11:09:38.069Z',
            'end_time' => '2020-10-31T11:18:38.069Z',
        ];

        $response = $client->request(
            'POST',
            '/daily_summaries',
            [
                'json' => $data,
                'base_uri' => 'https://localhost:8443'
            ]
        );
        $em = self::$container->get('doctrine')->getManager();
        $addedDs = $em->getRepository(DailySummary::class)->findBy(['dailySummary' => 'AutoUserSet']);
        self::assertEquals('norman@yazio.com', $addedDs[0]->getUser()->getEmail());
        self::assertEquals(201, $response->getStatusCode());
    }

    public function testAddTask()
    {
        $client = $this->createAuthenticatedClient();

        $data = [
            'notes' => 'New Task',
            'description' => 'Functional Test Task Description'
        ];

        $response = $client->request(
            'POST',
            '/tasks',
            [
                'json' => $data,
                'base_uri' => 'https://localhost:8443'
            ]
        );
        $em = self::$container->get('doctrine')->getManager();
        $addedTask = $em->getRepository(Task::class)->findBy(['description' => 'Functional Test Task Description']);
        self::assertEquals('norman@yazio.com', $addedTask[0]->getUser()->getEmail());
        self::assertEquals(201, $response->getStatusCode());
    }

    public function testAddTimer()
    {
        $client = $this->createAuthenticatedClient();

        $response = $client->request(
            'POST',
            '/timers',
            [
                'json' => [
                    'dateStart' => (new \DateTime('-4 hours'))->format('Y-m-d H:i:s'),
                    'dateEnd' => (new \DateTime('now'))->format('Y-m-d H:i:s'),
                    'timerType' => 'work'
                ],
                'base_uri' => 'https://localhost:8443'
            ]
        );
        $em = self::$container->get('doctrine')->getManager();
        $timers = $em->getRepository(Timer::class)->findAll();
        self::assertEquals('norman@yazio.com', $timers[count($timers)-1]->getUser()->getEmail());
        self::assertEquals(201, $response->getStatusCode());
    }
}
