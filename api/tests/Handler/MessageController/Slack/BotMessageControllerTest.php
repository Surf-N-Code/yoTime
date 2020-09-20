<?php

namespace App\Tests\Handler\MessageController\Slack;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Slack\SlashCommand;
use App\Tests\IntegrationTestCase;use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Serializer\Serializer;

class BotMessageControllerTest extends ApiTestCase
{

    public function testHiBotMention()
    {
        self::markTestSkipped();
        $data = [
            "event" => [
                "client_msg_id" => "a696521b-dab6-42ea-af69-9f904854c181",
                "type" => "app_mention",
                "text" => "hi /asdf <@ULGR2HKS7>",
                "user" => "UHW253RU1",
                "ts" => "1571559946.000600",
                "team" => "THW253RMX",
                "channel" => "GLH77MXNX",
                "event_ts" => "1571559946.000600",
                "challenge" => "challenge"
            ]
        ];

        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTPS'=> true,
            'HTTP_HOST' => 'localhost:8443'
        ];

        $payload = json_encode($data);
        $response = $this->request('POST', '/slack/bot/message', $payload, '', $headers);
        dump($response);
        self::assertEquals(200, $response->getStatusCode());
    }

    public function testSlackBotVerification()
    {
        self::markTestSkipped();
        $data = [
            "event" => [
                "type" => "url_verification",
                "token" => "QbBtIJowqMvCl1NcYexCG7rN",
                "challenge" => "DxUwifpaOL5VikG3y0eFOr9k3fHj0A7cKNR7eguIrd8KIM9oVzsP"
            ]
        ];

        $payload = json_encode($data);
        $client = static::createClient();
        $client->enableProfiler();
        $client->request('POST', '/slack/bot/message', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        $response = $client->getResponse();
        self::assertEquals(200, $response->getStatusCode());
    }
}
