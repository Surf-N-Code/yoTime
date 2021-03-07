<?php

namespace App\Tests\Handler\MessageController\Slack;

use App\Entity\Timer;
use App\Entity\TimerType;
use App\Tests\IntegrationTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

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
        $client = $this->createAuthenticatedClient();

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

        $runningTimers = $em->getRepository(Timer::class)->findBy(['dateEnd' => null, 'timerType' => TimerType::WORK]);
        self::assertEquals(201, $response->getStatusCode());
        self::assertCount(1, $runningTimers);
    }

    public function testHiBotEventAlreadyPunchedIn()
    {
        $data = $this->generateCommandData('hey');
        $client = $this->createAuthenticatedClient();

        $response = $client->request(
            'POST',
            '/slack/event/bot',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/json'),
                'base_uri' => 'https://localhost:8443'
            ]
        );

        $em = self::$container->get('doctrine')->getManager();
        $runningTimers = $em->getRepository(Timer::class)->findBy(['dateEnd' => null, 'timerType' => TimerType::WORK]);
        self::assertCount(1, $runningTimers);
        self::assertEquals(201, $response->getStatusCode());
    }

    public function testByeBotEvent()
    {
        $data = $this->generateCommandData('bye');
        $client = $this->createAuthenticatedClient();

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

        $runningTimers = $em->getRepository(Timer::class)->findBy(['timerType' => TimerType::WORK]);
        self::assertEquals(201, $response->getStatusCode());
        self::assertCount(1, $runningTimers);
        self::assertEquals(TimerType::WORK, $runningTimers[0]->getTimerType());
    }

    public function testByeBotEventMissingTimer()
    {
        $byeData = $this->generateCommandData('bye');
        $client = $this->createAuthenticatedClient();
        $em = self::$container->get('doctrine')->getManager();
        $runningTimer = $em->getRepository(Timer::class)->findBy(['timerType' => TimerType::WORK]);
        $em->remove($runningTimer[0]);
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

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testSlackBotVerification()
    {
        $data = [
            "type" => "url_verification",
            "token" => "QbBtIJowqMvCl1NcYexCG7rN",
            "challenge" => "DxUwifpaOL5VikG3y0eFOr9k3fHj0A7cKNR7eguIrd8KIM9oVzsP"
        ];

        $response = $this->createAuthenticatedClient()->request(
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
