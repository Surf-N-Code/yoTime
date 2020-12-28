<?php


namespace App\Tests\Mocks;


use App\Slack\SlackClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use TheSeer\Tokenizer\Exception;

class SlackClientMock extends SlackClient
{
    public function __construct(HttpClientInterface $slackClient)
    {
        parent::__construct($slackClient);
    }

    public function slackApiCall($method, $slackEndpoint, $data)
    {
    }

    public function slackWebhook($data)
    {
    }

    public function getSlackUserProfile($slackUserId)
    {
        $json = [
            'ok'   => true,
            'user' =>
                [
                    'id'                  => 'W012A3CDE',
                    'team_id'             => 'T012AB3C4',
                    'name'                => 'spengler',
                    'deleted'             => false,
                    'color'               => '9f69e7',
                    'real_name'           => 'Egon Spengler',
                    'tz'                  => 'America/Los_Angeles',
                    'tz_label'            => 'Pacific Daylight Time',
                    'tz_offset'           => -25200,
                    'profile'             =>
                        [
                            'avatar_hash'             => 'ge3b51ca72de',
                            'status_text'             => 'Print is dead',
                            'status_emoji'            => ':books:',
                            'real_name'               => 'Egon Spengler',
                            'display_name'            => 'spengler',
                            'real_name_normalized'    => 'Egon Spengler',
                            'display_name_normalized' => 'spengler',
                            'email'                   => 'spengler@ghostbusters.example.com',
                            'image_original'          => 'https://.../avatar/e3b51ca72dee4ef87916ae2b9240df50.jpg',
                            'image_24'                => 'https://.../avatar/e3b51ca72dee4ef87916ae2b9240df50.jpg',
                            'image_32'                => 'https://.../avatar/e3b51ca72dee4ef87916ae2b9240df50.jpg',
                            'image_48'                => 'https://.../avatar/e3b51ca72dee4ef87916ae2b9240df50.jpg',
                            'image_72'                => 'https://.../avatar/e3b51ca72dee4ef87916ae2b9240df50.jpg',
                            'image_192'               => 'https://.../avatar/e3b51ca72dee4ef87916ae2b9240df50.jpg',
                            'image_512'               => 'https://.../avatar/e3b51ca72dee4ef87916ae2b9240df50.jpg',
                            'team'                    => 'T012AB3C4',
                        ],
                    'is_admin'            => true,
                    'is_owner'            => false,
                    'is_primary_owner'    => false,
                    'is_restricted'       => false,
                    'is_ultra_restricted' => false,
                    'is_bot'              => false,
                    'updated'             => 1502138686,
                    'is_app_user'         => false,
                    'has_2fa'             => false,
                ],
        ];
        return new JsonResponse($json);
    }
}
