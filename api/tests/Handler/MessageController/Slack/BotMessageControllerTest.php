<?php

namespace App\Tests\Handler\MessageController\Slack;

use App\Entity\Timer;
use App\Tests\IntegrationTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class BotMessageControllerTest extends IntegrationTestCase
{
    use ReloadDatabaseTrait;

    private function generateCommandData($text)
    {
        return [
            "event" => [
                "client_msg_id" => "a696521b-dab6-42ea-af69-9f904854c181",
                "type" => "app_mention",
                "text" => "Moin, " . $text . " \u003C@ULGR2HKS7\u003E",
                "user" => "UHW253RU1",
                "ts" => "1571559946.000600",
                "team" => "THW253RMX",
                "channel" => "GLH77MXNX",
                "event_ts" => "1571559946.000600",
            ]
        ];
    }

    public function testHiBotEvent()
    {
        $data = $this->generateCommandData('hey');
        $client = static::createClient();

        $em = self::$container->get('doctrine')->getManager();
        $punchInTimer = $em->getRepository(Timer::class)->findBy(['dateEnd' => null, 'timerType' => 'punch']);
        $em->remove($punchInTimer[0]);
        $em->flush();

        $response = $client->request(
            'POST',
            '/slack/event/bot',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/json'),
                'base_uri' => 'https://localhost:8443'
            ]
        );

        $runningTimers = $em->getRepository(Timer::class)->findBy(['dateEnd' => null, 'timerType' => 'punch']);
        self::assertEquals(201, $response->getStatusCode());
        self::assertCount(1, $runningTimers);
        self::assertEquals('punch', $runningTimers[0]->getTimerType());
    }

    public function testHiBotEventAlreadyPunchedIn()
    {
        $data = $this->generateCommandData('hey');
        $client = static::createClient();

        $response = $client->request(
            'POST',
            '/slack/event/bot',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/json'),
                'base_uri' => 'https://localhost:8443'
            ]
        );

        self::assertEquals(412, $response->getStatusCode());
    }

    public function testByeBotEvent()
    {
        $data = $this->generateCommandData('bye');
        $client = static::createClient();

        $em = self::$container->get('doctrine')->getManager();

        $response = $client->request(
            'POST',
            '/slack/event/bot',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/json'),
                'base_uri' => 'https://localhost:8443'
            ]
        );

        $runningTimers = $em->getRepository(Timer::class)->findBy(['timerType' => 'punch']);
        self::assertEquals(201, $response->getStatusCode());
        self::assertCount(1, $runningTimers);
        self::assertEquals('punch', $runningTimers[0]->getTimerType());
    }

    public function testByeBotEventMissingTimer()
    {
        $byeData = $this->generateCommandData('bye');
        $client = static::createClient();

        $em = self::$container->get('doctrine')->getManager();
        $punchInTimer = $em->getRepository(Timer::class)->findBy(['dateEnd' => null, 'timerType' => 'punch']);
        $em->remove($punchInTimer[0]);
        $em->flush();

        $response = $client->request(
            'POST',
            '/slack/event/bot',
            [
                'json' => $byeData,
                'headers' => $this->getValidSlackHeaders($byeData, 'application/json'),
                'base_uri' => 'https://localhost:8443'
            ]
        );

        self::assertEquals(412, $response->getStatusCode());
    }

    public function testSlackBotVerification()
    {
        $data = [
            "type" => "url_verification",
            "token" => "QbBtIJowqMvCl1NcYexCG7rN",
            "challenge" => "DxUwifpaOL5VikG3y0eFOr9k3fHj0A7cKNR7eguIrd8KIM9oVzsP"
        ];

        $response = static::createClient()->request(
            'POST',
            '/slack/event/bot',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/x-www-form-urlencoded'),
                'base_uri' => 'https://localhost:8443'
            ]
        );
        $content = $response->getContent();
        $responseCode = $response->getStatusCode();
        self::assertEquals(200, $responseCode);
        self::assertEquals('DxUwifpaOL5VikG3y0eFOr9k3fHj0A7cKNR7eguIrd8KIM9oVzsP', $content);
    }
}
