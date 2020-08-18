<?php

namespace App\tests;

use App\Services\UserProvider;
use App\Services\SlackClient;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UserProviderTest extends TestCase
{

    public function testPopulateUserEntityFromSlackInfo()
    {
        $this->markTestSkipped();
    }

    public function testGetSlackUserInfo()
    {
        $this->markTestSkipped();
    }

    public function testGetDbUserBySlackId()
    {
        $this->markTestSkipped();
    }
}
