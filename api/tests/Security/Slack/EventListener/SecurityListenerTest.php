<?php

namespace App\Tests\Security\Slack\EventListener;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Slack\SlashCommand;
use App\Kernel;
use App\Security\Slack\EventListener\SecurityListener;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SecurityListenerTest extends ApiTestCase
{
    public function testWorkSlashCommandEvent()
    {
//        self::markTestSkipped();
        $response = static::createClient()->request(
            'POST',
            '/slack/slashcommand',
            [
                'json' => [
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
                ],
//                'headers' => [
//                    'X-Slack-Request-Timestamp' => 123123,
//                    'X-Slack-Signature' => 'v0=b12793eef34f4c725b152ea2192d08c6a8a8f718dafd3a23644be494a745dfed',
//                    'Content-Type' => 'application/ld+json'
//                ],
                'base_uri' => 'https://localhost:8443'
            ]
        );
//        $this->assertEquals(202, $response->getStatusCode());


//        $response = static::createClient()->request(
//            'GET',
//            '/tasks',
//            ['base_uri' => 'https://localhost:8443']
//        );
        $this->assertEquals(200, $response->getStatusCode());
    }
}
