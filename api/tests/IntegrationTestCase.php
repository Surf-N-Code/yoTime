<?php


namespace App\Tests;


use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class IntegrationTestCase extends ApiTestCase
{
    public const SLACK_SECRET = '41fc7fe005b809f97d50394d82442186';

    public function getValidSlackHeaders($data, $contentType)
    {
        $slackTimestamp = 1600676620;
        $string = sprintf('v0:%s:%s', $slackTimestamp, json_encode($data));
        $mySig = 'v0='.hash_hmac('sha256', $string, self::SLACK_SECRET);

        return [
            'x-slack-request-timestamp' => $slackTimestamp,
            'x-slack-signature' => $mySig,
            'content-type' => $contentType,
            'accept' => 'application/json'
        ];
    }
}
