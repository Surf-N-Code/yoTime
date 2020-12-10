<?php

namespace App\Tests\DataPersister;

use App\Entity\DailySummary;
use App\Entity\User;
use App\Tests\IntegrationTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Prophecy\PhpUnit\ProphecyTrait;

class DailySummaryPersisteIntegrationTest extends IntegrationTestCase
{
    use ReloadDatabaseTrait;
    use ProphecyTrait;

    public function testAddDailySummary()
    {
        $client = $this->createAuthenticatedClient();

        $data = [
            'daily_summary' => 'basic daily summary',
            'date' => '2019-01-02',
            'time_worked_in_s' => 23988,
            'time_break_in_s' => 238,
            'is_email_sent' => true,
            'is_synced_to_personio' => true,
            'start_time' => '2012-10-31T11:09:38.069Z',
            'end_time' => '2012-10-31T11:18:38.069Z',
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
        $addedDs = $em->getRepository(DailySummary::class)->findBy([],['id' => 'DESC'],1,0);
        self::assertEquals('norman@yazio.com', $addedDs[0]->getUser()->getEmail());
        self::assertEquals(201, $response->getStatusCode());
    }

    public function testAddDailySummaryDuplicateDate()
    {
        $client = $this->createAuthenticatedClient();

        $em = self::$container->get('doctrine')->getManager();
//        $user = $em->getRepository(User::class)->find(1);
//        $ds = new DailySummary();
//        $ds->setUser($user);
//        $ds->setDate(new \DateTime('2019-01-01'));
//        $ds->setDailySummary('Ds text');
//        $ds->setStartTime(new \DateTime('now'));
//        $ds->setEndTime((new \DateTime('now'))->modify('+6 hours'));
//        $ds->setTimeWorkedInS(45345);
//        $em->persist($ds);
//        $em->flush();

        $data = [
            'daily_summary' => 'basic daily summary',
            'date' => '2019-01-01',
            'time_worked_in_s' => 23988,
            'time_break_in_s' => 238,
            'is_email_sent' => true,
            'is_synced_to_personio' => true,
            'start_time' => '2012-10-31T11:09:38.069Z',
            'end_time' => '2012-10-31T11:18:38.069Z',
        ];

        $response = $client->request(
            'POST',
            '/daily_summaries',
            [
                'json' => $data,
                'base_uri' => 'https://localhost:8443'
            ]
        );

        self::assertEquals(409, $response->getStatusCode());
    }
}
