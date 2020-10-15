<?php

namespace App\Tests\Handler\MessageController\Slack;

use App\Entity\DailySummary;
use App\Entity\Timer;
use App\Entity\TimerType;
use App\Entity\User;
use App\Tests\IntegrationTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class InteractionEventControllerIntegrationTest extends IntegrationTestCase
{
    use ReloadDatabaseTrait;

    public function testDailySummarySubmit()
    {
        $data = [
            'payload' => '{"type":"view_submission","team":{"id":"THW253RMX","domain":"diltheymedia"},"user":{"id":"UHW253RU1","username":"ndilthey","name":"ndilthey","team_id":"THW253RMX"},"api_app_id":"ALTNUDXE0","token":"QbBtIJowqMvCl1NcYexCG7rN","trigger_id":"1359827201715.608073127745.2f087f09e939a0cc814be02608b7dc6c","view":{"id":"V01AKJ7HBSN","team_id":"THW253RMX","type":"modal","blocks":[{"type":"input","block_id":"daily_summary_block","label":{"type":"plain_text","text":"Tasks","emoji":true},"optional":false,"element":{"type":"plain_text_input","action_id":"summary_block_input","placeholder":{"type":"plain_text","text":"Add the tasks your completed here...","emoji":true},"multiline":true}},{"type":"input","block_id":"mail_block","label":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"optional":false,"element":{"type":"static_select","action_id":"mail_choice","placeholder":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"initial_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},"options":[{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},{"text":{"type":"plain_text","text":":x: no","emoji":true},"value":"false"}]}}],"private_metadata":"","callback_id":"ml_ds","state":{"values":{"daily_summary_block":{"summary_block_input":{"type":"plain_text_input","value":"My daily summary for today"}},"mail_block":{"mail_choice":{"type":"static_select","selected_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"}}}}},"hash":"1599938164.Fi7N1PWX","title":{"type":"plain_text","text":"Daily Summary","emoji":true},"clear_on_close":false,"notify_on_close":false,"close":null,"submit":{"type":"plain_text","text":"Send","emoji":true},"previous_view_id":null,"root_view_id":"V01AKJ7HBSN","app_id":"ALTNUDXE0","external_id":"","app_installed_team_id":"THW253RMX","bot_id":"BLU73PDGQ"},"response_urls":[]}'
        ];
        $client = static::createClient();

        $em = self::$container->get('doctrine')->getManager();
        $start = (new \DateTime())->modify('-600 minutes');
        $user = $em->getRepository(User::class)->find(1);
        $timer = new Timer();
        $timer->setUser($user);
        $timer->setDateStart($start);
        $timer->setTimerType(TimerType::WORK);
        $em->persist($timer);
        $em->flush();

        $response = $client->request(
            'POST',
            '/slack/event/interaction',
                [
                'json' => $data,
                'headers' => [
                    'x-slack-request-timestamp' => 1600676620,
                    'x-slack-signature' => 'v0=73beb837aeab3e1510b2c298810418d41c67056fb0d07a638bd844f50b87bdb8',
                    'content-type' => 'application/x-www-form-urlencoded',
                    'accept' => 'application/json'
                ],
                'base_uri' => 'https://localhost:8443'
            ]
        );
        $ds = $em->getRepository(DailySummary::class)->findAll();
        $latestDs = end($ds);
        self::assertEquals(200, $response->getStatusCode());
        self::assertStringContainsString('Signed you out for the day', $response->getContent());
        self::assertStringContainsString('You spent', $response->getContent());
        self::assertEquals('My daily summary for today', $latestDs->getDailySummary());
        self::assertTrue($latestDs->getIsEmailSent());
        self::assertTrue($latestDs->getIsSyncedToPersonio());
    }

    public function testDailySummaryNotPunchedIn()
    {
        $data = [
            'payload' => '{"type":"view_submission","team":{"id":"THW253RMX","domain":"diltheymedia"},"user":{"id":"UHW253RU1","username":"ndilthey","name":"ndilthey","team_id":"THW253RMX"},"api_app_id":"ALTNUDXE0","token":"QbBtIJowqMvCl1NcYexCG7rN","trigger_id":"1359827201715.608073127745.2f087f09e939a0cc814be02608b7dc6c","view":{"id":"V01AKJ7HBSN","team_id":"THW253RMX","type":"modal","blocks":[{"type":"input","block_id":"daily_summary_block","label":{"type":"plain_text","text":"Tasks","emoji":true},"optional":false,"element":{"type":"plain_text_input","action_id":"summary_block_input","placeholder":{"type":"plain_text","text":"Add the tasks your completed here...","emoji":true},"multiline":true}},{"type":"input","block_id":"mail_block","label":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"optional":false,"element":{"type":"static_select","action_id":"mail_choice","placeholder":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"initial_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},"options":[{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},{"text":{"type":"plain_text","text":":x: no","emoji":true},"value":"false"}]}}],"private_metadata":"","callback_id":"ml_ds","state":{"values":{"daily_summary_block":{"summary_block_input":{"type":"plain_text_input","value":"My daily summary for today"}},"mail_block":{"mail_choice":{"type":"static_select","selected_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"}}}}},"hash":"1599938164.Fi7N1PWX","title":{"type":"plain_text","text":"Daily Summary","emoji":true},"clear_on_close":false,"notify_on_close":false,"close":null,"submit":{"type":"plain_text","text":"Send","emoji":true},"previous_view_id":null,"root_view_id":"V01AKJ7HBSN","app_id":"ALTNUDXE0","external_id":"","app_installed_team_id":"THW253RMX","bot_id":"BLU73PDGQ"},"response_urls":[]}'
        ];

        $client = static::createClient();

        $em = self::$container->get('doctrine')->getManager();
        $punchInTimer = $em->getRepository(Timer::class)->findBy(['timerType' => TimerType::WORK]);
        $em->remove($punchInTimer[0]);
        $em->flush();
        $ds_before = $em->getRepository(DailySummary::class)->findAll();

        $response = $client->request(
            'POST',
            '/slack/event/interaction',
            [
                'json' => $data,
                'headers' => [
                    'x-slack-request-timestamp' => 1600676620,
                    'x-slack-signature' => 'v0=73beb837aeab3e1510b2c298810418d41c67056fb0d07a638bd844f50b87bdb8',
                    'content-type' => 'application/x-www-form-urlencoded',
                    'accept' => 'application/json'
                ],
                'base_uri' => 'https://localhost:8443'
            ]
        );
        $ds_after = $em->getRepository(DailySummary::class)->findAll();
        self::assertEquals(200, $response->getStatusCode());
        self::assertStringContainsString('Seems like you didn\u0027t sign in this morning', $response->getContent());
        self::assertEquals(count($ds_before), count($ds_after));
    }

    public function testDailySummaryUpdateAlreadyPunchedOut()
    {
        $data = [
            'payload' => '{"type":"view_submission","team":{"id":"THW253RMX","domain":"diltheymedia"},"user":{"id":"UHW253RU1","username":"ndilthey","name":"ndilthey","team_id":"THW253RMX"},"api_app_id":"ALTNUDXE0","token":"QbBtIJowqMvCl1NcYexCG7rN","trigger_id":"1359827201715.608073127745.2f087f09e939a0cc814be02608b7dc6c","view":{"id":"V01AKJ7HBSN","team_id":"THW253RMX","type":"modal","blocks":[{"type":"input","block_id":"daily_summary_block","label":{"type":"plain_text","text":"Tasks","emoji":true},"optional":false,"element":{"type":"plain_text_input","action_id":"summary_block_input","placeholder":{"type":"plain_text","text":"Add the tasks your completed here...","emoji":true},"multiline":true}},{"type":"input","block_id":"mail_block","label":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"optional":false,"element":{"type":"static_select","action_id":"mail_choice","placeholder":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"initial_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},"options":[{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},{"text":{"type":"plain_text","text":":x: no","emoji":true},"value":"false"}]}}],"private_metadata":"","callback_id":"ml_ds","state":{"values":{"daily_summary_block":{"summary_block_input":{"type":"plain_text_input","value":"Edited daily summary text"}},"mail_block":{"mail_choice":{"type":"static_select","selected_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"}}}}},"hash":"1599938164.Fi7N1PWX","title":{"type":"plain_text","text":"Daily Summary","emoji":true},"clear_on_close":false,"notify_on_close":false,"close":null,"submit":{"type":"plain_text","text":"Send","emoji":true},"previous_view_id":null,"root_view_id":"V01AKJ7HBSN","app_id":"ALTNUDXE0","external_id":"","app_installed_team_id":"THW253RMX","bot_id":"BLU73PDGQ"},"response_urls":[]}'
        ];

        $client = static::createClient();

        $punchInTime = (new \DateTime())->modify('-600 minutes');
        $punchOutTime = (new \DateTime())->modify('-10 minutes');
        $em = self::$container->get('doctrine')->getManager();
        $punchInTimer = $em->getRepository(Timer::class)->findBy(['timerType' => TimerType::WORK]);
        $punchInTimer[0]->setDateEnd($punchOutTime);
        $em->persist($punchInTimer[0]);
        $em->flush();

        $user = $em->getRepository(User::class)->find(1);
        $ds = new DailySummary();
        $ds->setUser($user);
        $ds->setStartTime($punchInTime);
        $ds->setEndTime($punchOutTime);
        $ds->setDailySummary('My daily summary for today');
        $ds->setTimeWorkedInS(3000);
        $ds->setTimeBreakInS(300);
        $ds->setDate($punchInTime);
        $em->persist($ds);
        $em->flush();

        $response = $client->request(
            'POST',
            '/slack/event/interaction',
            [
                'json' => $data,
                'headers' => [
                    'x-slack-request-timestamp' => 1600676620,
                    'x-slack-signature' => 'v0=d656d032dbb30948bde98aba3c1e500f03bfe75abd9a1502f29053db3dc8ddf9',
                    'content-type' => 'application/x-www-form-urlencoded',
                    'accept' => 'application/json'
                ],
                'base_uri' => 'https://localhost:8443'
            ]
        );
        $dsUpdated = $em->getRepository(DailySummary::class)->find($ds->getId());
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('Edited daily summary text', $dsUpdated->getDailySummary());
        self::assertStringContainsString('Summary sent', $response->getContent());
        self::assertTrue($ds->getIsEmailSent());
        self::assertTrue($ds->getIsSyncedToPersonio());
    }

    public function testDailySummaryNoEmailAndSignOut()
    {
        $data = [
            'payload' => '{"type":"view_submission","team":{"id":"THW253RMX","domain":"diltheymedia"},"user":{"id":"UHW253RU1","username":"ndilthey","name":"ndilthey","team_id":"THW253RMX"},"api_app_id":"ALTNUDXE0","token":"QbBtIJowqMvCl1NcYexCG7rN","trigger_id":"1359827201715.608073127745.2f087f09e939a0cc814be02608b7dc6c","view":{"id":"V01AKJ7HBSN","team_id":"THW253RMX","type":"modal","blocks":[{"type":"input","block_id":"daily_summary_block","label":{"type":"plain_text","text":"Tasks","emoji":true},"optional":false,"element":{"type":"plain_text_input","action_id":"summary_block_input","placeholder":{"type":"plain_text","text":"Add the tasks your completed here...","emoji":true},"multiline":true}},{"type":"input","block_id":"mail_block","label":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"optional":false,"element":{"type":"static_select","action_id":"mail_choice","placeholder":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"initial_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},"options":[{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},{"text":{"type":"plain_text","text":":x: no","emoji":true},"value":"false"}]}}],"private_metadata":"","callback_id":"ml_ds","state":{"values":{"daily_summary_block":{"summary_block_input":{"type":"plain_text_input","value":"My daily summary for today"}},"mail_block":{"mail_choice":{"type":"static_select","selected_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"false"}}}}},"hash":"1599938164.Fi7N1PWX","title":{"type":"plain_text","text":"Daily Summary","emoji":true},"clear_on_close":false,"notify_on_close":false,"close":null,"submit":{"type":"plain_text","text":"Send","emoji":true},"previous_view_id":null,"root_view_id":"V01AKJ7HBSN","app_id":"ALTNUDXE0","external_id":"","app_installed_team_id":"THW253RMX","bot_id":"BLU73PDGQ"},"response_urls":[]}'
        ];
        $client = static::createClient();

        $em = self::$container->get('doctrine')->getManager();
        $start = (new \DateTime())->modify('-600 minutes');
        $user = $em->getRepository(User::class)->find(1);
        $timer = new Timer();
        $timer->setUser($user);
        $timer->setDateStart($start);
        $timer->setTimerType(TimerType::WORK    );
        $em->persist($timer);
        $em->flush();


        $response = $client->request(
            'POST',
            '/slack/event/interaction',
            [
                'json' => $data,
                'headers' => [
                    'x-slack-request-timestamp' => 1600676620,
                    'x-slack-signature' => 'v0=4d1cd05e776c73a0ae65b522ac12ea6aee758029417ffdc6b86ca634f8e9725a',
                    'content-type' => 'application/x-www-form-urlencoded',
                    'accept' => 'application/json'
                ],
                'base_uri' => 'https://localhost:8443'
            ]
        );
        $ds = $em->getRepository(DailySummary::class)->findAll();
        $insertedDs = end($ds);
        self::assertEquals(200, $response->getStatusCode());
        self::assertStringContainsString('Signed you out for the day', $response->getContent());
        self::assertStringContainsString('You spent', $response->getContent());
        self::assertStringNotContainsString('Summary sent', $response->getContent());
        self::assertEquals('My daily summary for today', $insertedDs->getDailySummary());
        self::assertFalse($insertedDs->getIsEmailSent());
        self::assertTrue($insertedDs->getIsSyncedToPersonio());
    }
}
