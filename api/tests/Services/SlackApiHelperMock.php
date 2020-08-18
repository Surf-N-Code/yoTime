<?php


namespace App\Tests\Services;


use GuzzleHttp\Client;

class SlackApiHelperMock
{
    public function sendSlackMessage($data)
    {
        return true;
    }
}
