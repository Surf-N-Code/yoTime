<?php


namespace App\Slack;


use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SlackClient
{
    /** @var HttpClient */
    private $client;

    public function __construct(HttpClientInterface $slackClient)
    {
        $this->client = $slackClient;
    }

    public function slackApiCall(string $method, string $slackEndpoint, array $data)
    {
        $response = $this->client->request($method, $slackEndpoint, [
            'json' => $data
        ]);
dump(json_decode($response->getContent(), true));
        dump($method, $slackEndpoint, $data);
    }

    public function slackWebhook(array $data)
    {
        $responseUrl = $data['response_url'] ?? $_ENV['SLACK_WEBHOOK_URI'];
        unset($data['response_url']);
        $this->client->request('POST', $responseUrl, [
            'json' => $data
        ]);
    }

    public function getSlackUserProfile(string $slackUserId)
    {
        return $this->client->request('GET', sprintf('users.info?token=%s&user=%s',$_ENV['SLACK_OAUTH_TOKEN'], $slackUserId));
    }

    public function setUserStatus(array $data)
    {
        return $this->client->request('POST', 'users.profile.set', [
            'json' => $data
        ]);
    }
}
