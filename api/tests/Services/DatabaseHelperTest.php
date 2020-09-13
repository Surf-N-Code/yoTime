<?php

namespace App\Tests\Services;

use App\Entity\Timer;
use App\Services\DatabaseHelper;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\VarDumper\Cloner\Data;

class DatabaseHelperTest extends TestCase
{
    use ProphecyTrait;

    private $em;
    private $logger;
    private $databaseHelper;

    public function setup():void
    {
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->databaseHelper = new DatabaseHelper($this->em->reveal(), $this->logger->reveal());
    }

    public function testFlushAndPersist()
    {
        $timer = new Timer();
        $this->em->persist($timer)
            ->shouldBeCalled();
        $this->em->flush()
            ->shouldBeCalled();
        $this->databaseHelper->flushAndPersist($timer);
    }

    public function testFlushAndPersistException()
    {
        $timer = new Timer();
        $this->em->persist($timer)
                 ->shouldBeCalled();
        $this->em->flush()
                 ->shouldBeCalled()
                 ->willThrow(DBALException::class);
        $this->logger->error(Argument::type('string'))
            ->shouldBeCalled();

        $this->expectException(\RuntimeException::class);
        $this->databaseHelper->flushAndPersist($timer);
    }
}
