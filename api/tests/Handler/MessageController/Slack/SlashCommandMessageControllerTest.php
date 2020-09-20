<?php

namespace App\Tests\Handler\MessageController\Slack;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Slack\SlashCommand;

class SlashCommandMessageControllerTest extends ApiTestCase
{
    public function testWorkSlashCommandEvent()
    {
        self::markTestSkipped();
        $sc = new SlashCommand();
        $sc->setChannelId('654654');
        $sc->setChannelName('channelname');
        $sc->setCommand('work');
        $sc->setResponseUrl('https://hooks.slack.com/commands/1234/5678');
        $sc->setTeamId('team123');
        $sc->setText('text');
        $sc->setUserId('user123');
        $sc->setUserName('Norman');

        $data = [
            "token" => "QbBtIJowqMvCl1NcYexCG7rN",
            "team_domain" => "diltheymedia",
            "channmel_id" => "GLH77MXNX",
            "channel_nanme" => "privategroup",
            "user_id" => "UHW253RU1",
            "user_name" => "ndilthey",
            "team_id" => "THW253RMX",
            "ts" => "1571559946.000600",
            "command" => "/work",
            "trigger_id" => "1274946476807.608073127745.5d3143756558cfeeb528ccbb7dad531e",
            "response_url" => ""
        ];

        $timeEntry = [
            "date_start" => "2020-08-15T08:01:00+00:00",
            "task" => "tasks/1",
            "user" => "users/1",
            "timer_type" => "work"
        ];

//        $payload = http_build_query($data);
//        dump($payload);
//        $response = $this->request('GET', '/timers');
//        $response = $this->request('GET', '/time_entries.json');
//        dump($response->getStatusCode());
//        dd($response->getContent());
//        $data = json_decode($response->getContent(), true);
//        dd($data);

//        dd($response);
//        self::assertEquals(200, $response->getStatusCode());

        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        $response = static::createClient()->request('GET', '/slack/slashcommand');

        dump($response);
        $this->assertResponseIsSuccessful();
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
        dd($response);
        self::assertEquals(200, $response->getStatusCode());
    }
}
