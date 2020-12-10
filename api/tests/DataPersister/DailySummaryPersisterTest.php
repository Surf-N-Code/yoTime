<?php


namespace App\Tests\DataPersister;


use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\DataPersister\DailySummaryPersister;
use App\Entity\DailySummary;
use App\Entity\User;
use App\Mail\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class DailySummaryPersisterTest extends TestCase
{
    use ProphecyTrait;

    private $decorated;
    private $em;
    private $unitOfWork;
    private $ds;
    private $mailer;
    private DailySummaryPersister $dailySummaryPersister;

    public function setup(): void
    {
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->unitOfWork = $this->prophesize(UnitOfWork::class);
        $this->mailer = $this->prophesize(Mailer::class);
        $this->decorated = $this->prophesize(ContextAwareDataPersisterInterface::class);
        $this->ds = $this->prophesize(DailySummary::class);
        $this->dailySummaryPersister = new DailySummaryPersister(
            $this->decorated->reveal(),
            $this->mailer->reveal(),
            $this->em->reveal()
        );
    }

    public function testPersistDs()
    {
        $this->decorated->persist($this->ds->reveal(), ['collection_operation_name' => 'post'])
            ->shouldBeCalled();

        $this->em->getUnitOfWork()
            ->shouldBeCalled()
            ->willReturn($this->unitOfWork);

        $this->unitOfWork->getOriginalEntityData($this->ds->reveal())
            ->shouldBeCalled()
            ->willReturn(['isEmailSent' => false]);

        $this->ds->getUser()
            ->shouldBeCalled()
            ->willReturn(new User());

        $this->ds->getDailySummary()
            ->shouldBeCalled()
            ->willReturn('DS Text');

        $this->mailer->send(Argument::type('string'), Argument::type('string'), Argument::type('string'), 'DS Text')
            ->shouldBeCalled();

        $this->dailySummaryPersister->persist($this->ds->reveal(), ['collection_operation_name' => 'post']);
    }

    public function testPersistDsWithoutMail()
    {
        $this->decorated->persist($this->ds->reveal(), ['collection_operation_name' => 'patch'])
                        ->shouldBeCalled();

        $this->em->getUnitOfWork()
                 ->shouldBeCalled()
                 ->willReturn($this->unitOfWork);

        $this->unitOfWork->getOriginalEntityData($this->ds->reveal())
                         ->shouldBeCalled()
                         ->willReturn(['isEmailSent' => true]);

        $this->mailer->send(Argument::type('string'), Argument::type('string'), Argument::type('string'), 'DS Text')
                     ->shouldNotBeCalled();

        $this->dailySummaryPersister->persist($this->ds->reveal(), ['collection_operation_name' => 'patch']);
    }
}
