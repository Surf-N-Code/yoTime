<?php

namespace App\Tests\Handler\MessageController\Slack;

use App\Entity\Timer;
use App\Entity\TimerType;
use App\Entity\User;
use App\Tests\IntegrationTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class SlashCommandMessageControllerTest extends IntegrationTestCase
{
    use ReloadDatabaseTrait;
    private function generateCommandData($command, $text = "")
    {
        return [
            "team_id" => "THW253RMX",
            "team_domain" => "diltheymedia",
            "channel_id" => "GLH77MXNX",
            "channel_name" => "privategroup",
            "user_id" => "UHW253RU1",
            "user_name" => "ndilthey",
            "command" => "/".$command,
            "text" => $text,
            "api_app_id" => "ALTNUDXE0",
            "response_url" => "",
            "trigger_id" => "1376434055859.608073127745.6b6bf1eec2610d38762c05c6f1decc7e",
        ];
    }

    public function testWorkCommand()
    {
        $data = $this->generateCommandData(TimerType::WORK);
        $client = $this->createAuthenticatedClient();
        $response = $client->request(
            'POST',
            '/slack/slashcommand',
                [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/x-www-form-urlencoded'),
                'base_uri' => 'https://localhost:8443'
            ]
        );

        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testBreakCommand()
    {
        $data = $this->generateCommandData(TimerType::BREAK);
        $client = $this->createAuthenticatedClient();
        $response = $client->request(
            'POST',
            '/slack/slashcommand',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/x-www-form-urlencoded'),
                'base_uri' => 'https://localhost:8443'
            ]
        );

        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testLateHiCommandInvalid()
    {
        $data = $this->generateCommandData('late_hi', '25:32');

        $client = $this->createAuthenticatedClient();
        $response = $client->request(
            'POST',
            '/slack/slashcommand',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/x-www-form-urlencoded'),
                'base_uri' => 'https://localhost:8443'
            ]
        );

        self::assertEquals(Response::HTTP_PRECONDITION_FAILED, $response->getStatusCode());
    }

    public function testLateHiCommand()
    {
        $data = $this->generateCommandData('late_hi', '07:33');
        $client = $this->createAuthenticatedClient();

        $this->truncateTableForClass(Timer::class);

        $response = $client->request(
            'POST',
            '/slack/slashcommand',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/x-www-form-urlencoded'),
                'base_uri' => 'https://localhost:8443'
            ]
        );

        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testLateBreakCommandInvalid()
    {
        $data = $this->generateCommandData('late_break', '25:32');
        $client = $this->createAuthenticatedClient();
        $response = $client->request(
            'POST',
            '/slack/slashcommand',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/x-www-form-urlencoded'),
                'base_uri' => 'https://localhost:8443'
            ]
        );

        self::assertEquals(Response::HTTP_PRECONDITION_FAILED, $response->getStatusCode());
    }

    public function testLateBreakCommand()
    {
        $data = $this->generateCommandData('late_break', '01:33');
        $client = $this->createAuthenticatedClient();
        $response = $client->request(
            'POST',
            '/slack/slashcommand',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/x-www-form-urlencoded'),
                'base_uri' => 'https://localhost:8443'
            ]
        );

        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testEndBreakCommand()
    {
        $data = $this->generateCommandData('end_break');
        $client = $this->createAuthenticatedClient();

        $em = self::$container->get('doctrine')->getManager();
        $start = (new \DateTime())->modify('-600 minutes');
        $user = $em->getRepository(User::class)->find(1);
        $timer = new Timer();
        $timer->setUser($user);
        $timer->setDateStart($start);
        $timer->setTimerType(TimerType::BREAK);
        $em->persist($timer);
        $em->flush();

        $response = $client->request(
            'POST',
            '/slack/slashcommand',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/x-www-form-urlencoded'),
                'base_uri' => 'https://localhost:8443'
            ]
        );

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testEndWorkCommand()
    {
        $data = $this->generateCommandData('end_work');
        $client = $this->createAuthenticatedClient();

        $em = self::$container->get('doctrine')->getManager();
        $start = (new \DateTime())->modify('-600 minutes');
        $user = $em->getRepository(User::class)->find(1);
        $timer = new Timer();
        $timer->setUser($user);
        $timer->setDateStart($start);
        $timer->setTimerType(TimerType::WORK);
        $em->persist($timer);
        $em->flush();

        $response = $client->request(
            'POST',
            '/slack/slashcommand',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/x-www-form-urlencoded'),
                'base_uri' => 'https://localhost:8443'
            ]
        );

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testUnrecognizedCommand()
    {
        $data = $this->generateCommandData('some_command');

        $client = $this->createAuthenticatedClient();
        $response = $client->request(
            'POST',
            '/slack/slashcommand',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/x-www-form-urlencoded'),
                'base_uri' => 'https://localhost:8443'
            ]
        );
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testDsCommand()
    {
        $data = $this->generateCommandData('ds');

        $client = $this->createAuthenticatedClient();
        $response = $client->request(
            'POST',
            '/slack/slashcommand',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/x-www-form-urlencoded'),
                'base_uri' => 'https://localhost:8443'
            ]
        );
        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }
}
