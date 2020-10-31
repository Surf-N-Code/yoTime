<?php

namespace App\Tests\Doctrine;

use App\Entity\DailySummary;
use App\Entity\Task;
use App\Entity\Timer;
use App\Tests\IntegrationTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class UserFilterQueryExtensionText extends IntegrationTestCase
{
    use RefreshDatabaseTrait;

    public function testGetUserTimers()
    {
        $client = $this->createAuthenticatedClient();

        $response = $client->request(
            'GET',
            '/timers',
        );
        $data = json_decode($response->getContent(), true);
        $objects = array_filter($data['hydra:member'], static function($val) {
            return $val['user'] !== '/users/1';
        });

        self::assertTrue(empty($objects));
        self::assertEquals(200, $response->getStatusCode());
    }

    public function testGetUserDailySummaries()
    {
        $client = $this->createAuthenticatedClient();

        $response = $client->request(
            'GET',
            '/daily_summaries',
        );
        $data = json_decode($response->getContent(), true);
        $objects = array_filter($data['hydra:member'], static function($val) {
            return $val['user'] !== '/users/1';
        });

        self::assertTrue(empty($objects));
        self::assertEquals(200, $response->getStatusCode());
    }

    public function testGetUserTasks()
    {
        $client = $this->createAuthenticatedClient();

        $response = $client->request(
            'GET',
            '/tasks',
        );
        $data = json_decode($response->getContent(), true);
        $objects = array_filter($data['hydra:member'], static function($val) {
            return $val['user'] !== '/users/1';
        });

        self::assertTrue(empty($objects));
        self::assertEquals(200, $response->getStatusCode());
    }
}
