<?php


namespace App\Tests\Mocks;


use App\Slack\SlackClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
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
        throw new \Exception('This function is mocked by Norman in SlackClientMock.php');
    }
}
