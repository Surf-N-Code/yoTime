<?php


namespace App\Slack;


use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SlackClient
{
    /** @var HttpClient */
    private $client;

    public function __construct(HttpClientInterface $slackClient)
    {
        $this->client = $slackClient;
    }

    public function slackApiCall($method, $slackEndpoint, $data)
    {
        $this->client->request($method, $slackEndpoint, [
            'json' => $data
        ]);
    }

    public function slackWebhook($data)
    {
        $responseUrl = $data['response_url'] ?? $_ENV['SLACK_WEBHOOK_URI'];
        unset($data['response_url']);
        $this->client->request('POST', $responseUrl, [
            'json' => $data
        ]);
    }

    public function getSlackUserProfile($slackUserId)
    {
        return $this->client->request('GET', sprintf('users.info?token=%s&user=%s',$_ENV['SLACK_OAUTH_TOKEN'], $slackUserId));
    }
}
