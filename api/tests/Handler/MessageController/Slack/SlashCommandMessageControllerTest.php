<?php

namespace App\Tests\Handler\MessageController\Slack;

use App\Entity\Timer;
use App\Entity\TimerType;
use App\Entity\User;
use App\Tests\IntegrationTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

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
        $data = $this->generateCommandData('work');
        $kernel = static::bootKernel();
        $container = $kernel->getContainer();

        $response = static::createClient()->request(
            'POST',
            '/slack/slashcommand',
                [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/x-www-form-urlencoded'),
                'base_uri' => 'https://localhost:8443'
            ]
        );

        $entityManager = $container->get('doctrine')->getManager();
        $runningTimers = $entityManager->getRepository(Timer::class)->findBy(['dateEnd' => null, 'timerType' => TimerType::WORK]);

        self::assertCount(1, $runningTimers);
        self::assertEquals(TimerType::WORK, $runningTimers[0]->getTimerType());
        self::assertEquals(201, $response->getStatusCode());
    }

    public function testBreakCommand()
    {
        $data = $this->generateCommandData('break');
        $kernel = static::bootKernel();
        $container = $kernel->getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $response = static::createClient()->request(
            'POST',
            '/slack/slashcommand',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/x-www-form-urlencoded'),
                'base_uri' => 'https://localhost:8443'
            ]
        );

        $runningTimers = $entityManager->getRepository(Timer::class)->findBy(['dateEnd' => null, 'timerType' => TimerType::BREAK]);

        self::assertCount(1, $runningTimers);
        self::assertEquals(TimerType::BREAK, $runningTimers[0]->getTimerType());
        self::assertEquals(201, $response->getStatusCode());
    }

    public function testLateHiCommandInvalid()
    {
        $data = $this->generateCommandData('late_hi', '25:32');

        $client = self::createClient();

        $em = self::$container->get('doctrine')->getManager();
//        $punchInTime = (new \DateTime())->modify('-600 minutes');
//        $punchInTimer = $em->getRepository(Timer::class)->findBy(['dateEnd' => null, 'timerType' => TimerType::WORK]);
//        $punchInTimer[0]->setDateEnd($punchInTime);
//        $em->persist($punchInTimer[0]);
//        $em->flush();

        $response = $client->request(
            'POST',
            '/slack/slashcommand',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/x-www-form-urlencoded'),
                'base_uri' => 'https://localhost:8443'
            ]
        );

        $runningTimers = $em->getRepository(Timer::class)->findBy(['dateEnd' => null, 'timerType' => 'punch']);

        self::assertCount(0, $runningTimers);
        self::assertEquals(412, $response->getStatusCode());
    }

    public function testLateHiCommand()
    {
        $data = $this->generateCommandData('late_hi', '07:33');
        $client = self::createClient();

        $em = self::$container->get('doctrine')->getManager();
        $punchInTimer = $em->getRepository(Timer::class)->findBy(['timerType' => TimerType::WORK]);
        $em->remove($punchInTimer[0]);
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

        $runningTimers = $em->getRepository(Timer::class)->findBy(['dateEnd' => null, 'timerType' => TimerType::WORK]);

        self::assertCount(1, $runningTimers);
        self::assertEquals(201, $response->getStatusCode());
    }

    public function testLateBreakCommandInvalid()
    {
        $data = $this->generateCommandData('late_break', '25:32');
        $kernel = static::bootKernel();
        $container = $kernel->getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $runningTimersBefore = count($entityManager->getRepository(Timer::class)->findBy(['timerType' => TimerType::BREAK]));

        $response = static::createClient()->request(
            'POST',
            '/slack/slashcommand',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/x-www-form-urlencoded'),
                'base_uri' => 'https://localhost:8443'
            ]
        );

        $runningTimersAfter = count($entityManager->getRepository(Timer::class)->findBy(['timerType' => TimerType::BREAK]));
        self::assertEquals($runningTimersBefore, $runningTimersAfter);
        self::assertEquals(400, $response->getStatusCode());
    }

    public function testLateBreakCommand()
    {
        $data = $this->generateCommandData('late_break', '01:33');
        $kernel = static::bootKernel();
        $container = $kernel->getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $response = static::createClient()->request(
            'POST',
            '/slack/slashcommand',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/x-www-form-urlencoded'),
                'base_uri' => 'https://localhost:8443'
            ]
        );

        $runningTimers = $entityManager->getRepository(Timer::class)->findBy(['timerType' => TimerType::BREAK]);

        $createdTimer = null;
        foreach ($runningTimers as $index => $runningTimer) {
            if ($runningTimer->getDateEnd() !== null && $runningTimer->getDateEnd() == (new \DateTime('now'))->setTime(02,33,0)) {
                $createdTimer = $runningTimer;
            }
        }
        self::assertEquals(TimerType::BREAK, $createdTimer->getTimerType());
        self::assertEquals((new \DateTime('now'))->setTime(01,0,0), $createdTimer->getDateStart());
        self::assertNotnUll($createdTimer->getDateEnd());
        self::assertEquals(201, $response->getStatusCode());
    }

    public function testEndBreakCommand()
    {
        $data = $this->generateCommandData('end_break');
        $client = self::createClient();

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

        $runningTimer = $em->getRepository(Timer::class)->findBy(['timerType' => TimerType::BREAK]);
        self::assertEquals(TimerType::BREAK, $runningTimer[0]->getTimerType());
        self::assertNotnUll($runningTimer[0]->getDateEnd());
        self::assertEquals(201, $response->getStatusCode());
    }

    public function testEndWorkCommand()
    {
        $data = $this->generateCommandData('end_work');
        $client = self::createClient();

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

        $runningTimer = $em->getRepository(Timer::class)->findBy(['timerType' => TimerType::WORK]);
        self::assertEquals(TimerType::WORK, $runningTimer[0]->getTimerType());
        self::assertNotnUll($runningTimer[0]->getDateEnd());
        self::assertEquals(201, $response->getStatusCode());
    }

    public function testUnrecognizedCommand()
    {
        $data = $this->generateCommandData('some_command');

        $response = static::createClient()->request(
            'POST',
            '/slack/slashcommand',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/x-www-form-urlencoded'),
                'base_uri' => 'https://localhost:8443'
            ]
        );
        self::assertEquals(201, $response->getStatusCode());
    }

    public function testDsCommand()
    {
        $data = $this->generateCommandData('ds');

        $response = static::createClient()->request(
            'POST',
            '/slack/slashcommand',
            [
                'json' => $data,
                'headers' => $this->getValidSlackHeaders($data, 'application/x-www-form-urlencoded'),
                'base_uri' => 'https://localhost:8443'
            ]
        );
        self::assertEquals(201, $response->getStatusCode());
    }
}
