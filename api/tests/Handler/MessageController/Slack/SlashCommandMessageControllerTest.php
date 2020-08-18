<?php

namespace App\Tests\Handler\MessageController\Slack;

use App\Entity\Slack\SlashCommand;
use App\Tests\IntegrationTestCase;

class SlashCommandMessageControllerTest extends IntegrationTestCase
{
    public function testWorkEvent()
    {
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
            "response_url" => "https://hooks.slack.com/commands/THW253RMX/1289688930754/oxcAdThmZqvmXH2Ln7llYTO8"
        ];

        $payload = http_build_query($data);
//        dump($payload);
//        $response = $this->request('POST', '/slack/slashcommand', $payload);
        $response = $this->request('GET', '/time_entries.json');
//        dd($response->getContent());
//        $data = json_decode($response->getContent(), true);
//        dd($data);

//        dd($response);
        self::assertEquals(200, $response->getStatusCode());
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
        $client->request('POST', '/slack/bot/message', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        $response = $client->getResponse();
//        dump($response);
        self::assertEquals(200, $response->getStatusCode());
    }
}
