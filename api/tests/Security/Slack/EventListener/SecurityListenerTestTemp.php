<?php

namespace App\Tests\Security\Slack\EventListener;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Slack\SlashCommand;
use App\Kernel;
use App\Security\Slack\EventListener\SecurityListener;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SecurityListenerTestTemp extends ApiTestCase
{
    //$event = $this->createResponseEvent($request, HttpKernelInterface::MASTER_REQUEST, $response);

//$request = new Request();
//$exception = new \Exception();
//$event = new GetResponseForExceptionEvent(
//$this->createMock(HttpKernelInterface::class),
//$request,
//HttpKernelInterface::MASTER_REQUEST,
//$exception
//);
//$listener = new ExceptionListener($entrypointLookupCollection, $buildNames);
//$listener->onKernelException($event);

    public function testWorkSlashCommandEvent()
    {
//        $response = static::createClient([], ['base_uri' => 'https://localhost:8443'])->request(
//            'POST',
//            '/tasks',
//            [
//                'json' => [
//                    "description" => "description",
//                    "billable" => true,
//                    "notes" => "Note",
//                    "user" => "users/3"
//                ],
////                'headers' => [
////                    'X-Slack-Request-Timestamp' => 123123,
////                    'X-Slack-Signature' => 'v0=b12793eef34f4c725b152ea2192d08c6a8a8f718dafd3a23644be494a745dfed',
////                    'Content-Type' => 'application/ld+json'
////                ],
//                'base_uri' => 'https://localhost:8443'
//            ]
//        );
//        dd($response);
//        $client = static::createClient();
//
//        $client->request('GET', 'https://localhost:8443/tasks');
//
//        dump(json_decode($client->getResponse()->getContent()));
//        dd(json_decode($client->getResponse()));
//        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $response = static::createClient()->request(
            'GET',
            '/tasks',
            ['base_uri' => 'https://localhost:8443']
        );
        dd($response);
        $this->assertEquals(202, $response->getStatusCode());
    }
}
