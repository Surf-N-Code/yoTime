<?php

namespace App\Tests\Services;

use App\Entity\User;
use App\Services\DateTimeProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DateTimeProviderTest extends TestCase
{

    use ProphecyTrait;
    public function getLocalUserTimeDataProvider()
    {
        // offset returned from User::getTimeZoneOffset; server time; expected local time
        return [
            [0, '2018-03-04 23:23:23', '2018-03-04 23:23:23'],
            [-180, '2018-03-04 23:23:23', '2018-03-04 23:20:23'],
            [180, '2018-03-04 23:23:23', '2018-03-04 23:26:23'],
        ];
    }

    /**
     * @dataProvider getLocalUserTimeDataProvider
     */
    public function testgetLocalUserTime(int $timeZoneOffset, string $serverTime, string $expectedLocalTime)
    {
        $dateTimeProvider = new DateTimeProvider();

        $user = $this->prophesize(User::class);
        $user->getTzOffset()
             ->shouldBeCalled()
             ->willReturn($timeZoneOffset);

        $now = new \DateTime($serverTime);

        $localTime = $dateTimeProvider->convertToLocalUserTime($now, $user->reveal());

        self::assertEquals($expectedLocalTime, $localTime->format('Y-m-d H:i:s'));
    }
}
