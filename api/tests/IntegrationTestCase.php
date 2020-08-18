<?php


namespace App\Tests;


use Liip\FunctionalTestBundle\Test\WebTestCase;

class IntegrationTestCase extends WebTestCase
{
    protected static $application;
    protected static function createClient(array $options = [], array $server = [])
    {
        return parent::createClient($options,$server);
    }

    protected function request(...$args)
    {
        return $this->requestClient(...$args)->getResponse();
    }

    protected function requestClient(string $method, string $uri, string $content = null, string $token = null, array $headers = [], bool $enableProfiler = false, array $files = [])
    {
        self::ensureKernelShutdown();
        $client = $this->makeClient();
        $_SERVER['APP_ENV'] = 'dev';

        $client->enableProfiler();

        $headers['HTTP_Authorization'] = 'Bearer '.$token;

        $headers = array_merge([
            'CONTENT_TYPE' => 'application/json', //@todo change for betmessage event
//            'HTTPS'=> true,
//            'HTTP_HOST' => 'localhost:8443'
        ], $headers);

        $client->request($method, $uri, [], $files, $headers, $content);

        return $client;

//        $basicAuthCredentials = [];
//        if (strpos($uri, '/frontend/') !== false) {
//            $basicAuthCredentials['PHP_AUTH_USER'] = 'frontend';
//            $basicAuthCredentials['PHP_AUTH_PW'] = $_SERVER['FRONTEND_CLIENT_PASSWORD'];
//        }
//        if (strpos($uri, '/test/') !== false) {
//            $basicAuthCredentials['PHP_AUTH_USER'] = 'check';
//            $basicAuthCredentials['PHP_AUTH_PW'] = 't6ycbDqoWgXnPtK';
//        }
//
//        self::ensureKernelShutdown();
//        $client = static::makeClient($basicAuthCredentials);
//
//        if ($enableProfiler) {
//            $client->enableProfiler();
//        }
//
//        $headers['HTTP_Authorization'] = 'Bearer ' . $token;
//
//        $headers = array_merge([
//            'CONTENT_TYPE' => 'application/json',
//        ], $headers);
//
//        $client->request($method, $uri, [], $files, $headers, $content);
//
//        return $client;
    }
}
