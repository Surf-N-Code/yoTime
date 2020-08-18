<?php


namespace App\Slack;


use Symfony\Component\HttpClient\HttpClient;

class SlackClient
{
    /** @var HttpClient */
    private $client;
//    protected static $instance;

//    public static function getClient()
//    {
//        if (null === self::$instance) {
//            self::$instance = new self();
//            self::$instance::clientSetup();
//        }
//        return self::$client;
//    }
//
//    protected function __clone() {}

//    private static function clientSetup()
//    {
//        $client = HttpClient::create([
//            'auth_bearer' => $_ENV['SLACK_BOT_TOKEN'],
//            'base_uri' => 'http://localhost:8080/'
//        ]);
//        self::$client = $client;
//    }
    public function __construct()
    {
        $this->client = HttpClient::create([
            'auth_bearer' => $_ENV['SLACK_BOT_TOKEN'],
            'base_uri' => 'https://slack.com/api/'
        ]);
    }

    const DEFAULT_WEBHOOK = 'https://hooks.slack.com/services/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';

    public function sendEphemeral($data)
    {
        $this->client->request('POST', 'chat.postEphemeral', [
            'json' => $data
        ]);
    }

    public function sendWebhook($data)
    {
        dump($data);
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
