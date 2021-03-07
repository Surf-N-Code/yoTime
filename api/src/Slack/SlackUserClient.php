<?php


namespace App\Slack;


use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SlackUserClient
{
    /** @var HttpClient */
    private $client;

    public function __construct(HttpClientInterface $slackUserClient)
    {
        $this->client = $slackUserClient;
    }

    public function slackApiCall(string $method, string $slackEndpoint, array $data)
    {
        $response = $this->client->request($method, $slackEndpoint, [
            'json' => $data
        ]);
dump(json_decode($response->getContent(), true));
        dump($method, $slackEndpoint, $data);
    }
}
