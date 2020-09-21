<?php

namespace App\Tests\Handler\MessageController\Slack;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class SlashCommandMessageControllerTest extends ApiTestCase
{
    public function testWorkCommandSc()
    {
        $data = [
            "team_id" => "THW253RMX",
            "team_domain" => "diltheymedia",
            "channel_id" => "GLH77MXNX",
            "channel_name" => "privategroup",
            "user_id" => "UHW253RU1",
            "user_name" => "ndilthey",
            "command" => "/work",
            "text" => "",
            "api_app_id" => "ALTNUDXE0",
            "response_url" => "",
            "trigger_id" => "1376434055859.608073127745.6b6bf1eec2610d38762c05c6f1decc7e",
        ];

        $response = static::createClient()->request(
            'POST',
            '/slack/slashcommand',
                [
                'json' => $data,
                'headers' => [
                    'x-slack-request-timestamp' => 1600676620,
                    'x-slack-signature' => 'v0=27e5426d075e6d68333fb2aaae9794310a2bde8eee2c551a16828b7eadb3da10',
                    'content-type' => 'application/x-www-form-urlencoded',
                    'accept' => 'application/json'
                ],
                'base_uri' => 'https://localhost:8443'
            ]
        );
        $this->assertEquals(202, $response->getStatusCode());
    }

    public function testSlackBotVerification()
    {
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
        $response = static::createClient()->request('POST', '/slack/bot/message', [
            'json' => $payload
        ]);
        self::assertEquals(200, $response->getStatusCode());
    }
}
