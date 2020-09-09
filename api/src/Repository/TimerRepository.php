<?php

namespace App\Repository;

use App\Entity\SlackTeam;
use App\Entity\Timer;
use App\Entity\TimerType;
use App\Entity\User;
use App\Services\DateTimeProvider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Timer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Timer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Timer[]    findAll()
 * @method Timer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimerRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    private DateTimeProvider $dateTimeProvider;

    public function __construct(
        ManagerRegistry $registry,
        EntityManagerInterface $em,
        DateTimeProvider $dateTimeProvider
    )
    {
        $this->em = $em;
        $this->dateTimeProvider = $dateTimeProvider;
        parent::__construct($registry, Timer::class);
    }

    /**
     * @param \App\Entity\User      $user
     * @param \DateTime    $userLocalTime
     * @param string                $constraint
     * @param bool $teamReport
     * @param \App\Entity\TimerType|null $timerType
     * @param string|null $slackTeamId
     *
     * @param \App\Entity\User|null $slackUserToReport
     *
     * @return Timer[] Returns an array of Timer objects
     * @throws \Exception
     */
    public function findTimeEntriesByPeriod(
        User $user,
        \DateTime $userLocalTime,
        string $constraint,
        bool $teamReport,
        string $timerType = null,
        string $slackTeamId = null,
        User $slackUserToReport = null
    ): ?array {
        $userLocalTime = new \DateTime($userLocalTime->format('Y-m-d H:i:s'));
        $weekStart = clone $userLocalTime;
        $monthStart = clone $userLocalTime;
        $yearStart = clone $userLocalTime;
        $weekStart = clone $weekStart->sub(new \DateInterval(sprintf('P%sD', (date('N')-1))))->setTime(0,0,0);
        $monthStart = clone $monthStart->sub(new \DateInterval(sprintf('P%sD', (date('j')-1))))->setTime(0,0,0);
        $yearStart = clone $yearStart->modify('first day of January this year');
        $weekEnd = clone $weekStart;
        $monthEnd = clone $monthStart;
        $yearEnd = clone $yearStart;
        $weekEnd->add(new \DateInterval('P6D'))->setTime(23,59,59);
        $monthEnd->modify('last day of this month')->setTime(23, 59, 59);
        $yearEnd->modify('last day of December this year')->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('t');

        switch ($constraint) {
            case 'day':
                $qb->andWhere('t.dateStart >= :dayStart')
                   ->andWhere('t.dateStart <= :dayEnd')
                   ->setParameter('dayStart', (new \DateTime())->setTime(0,0,0))
                   ->setParameter('dayEnd', (new \DateTime())->setTime(23,59,59));
                break;
            case 'week':
                $qb->andWhere('t.dateStart >= :weekStart')
                   ->andWhere('t.dateStart <= :weekEnd')
                   ->setParameter('weekStart', $weekStart)
                   ->setParameter('weekEnd', $weekEnd);
                break;
            case 'month':
                $qb->andWhere('t.dateStart >= :monthStart')
                   ->andWhere('t.dateStart <= :monthEnd')
                   ->setParameter('monthStart', $monthStart)
                   ->setParameter('monthEnd', $monthEnd);
                break;
            case 'year':
                $qb->andWhere('t.dateStart >= :yearStart')
                   ->andWhere('t.dateStart <= :yearEnd')
                   ->setParameter('yearStart', $yearStart)
                   ->setParameter('yearEnd', $yearEnd);
                break;
            case 'all':
                break;
        }

        if ($timerType) {
            $qb->andWhere('t.timerType = :timerType')
               ->setParameter('timerType', $timerType);
        }

        if ($teamReport) {
            $qb->leftJoin(User::class, 'u', 'WITH', 't.user = u.id')
               ->leftJoin(SlackTeam::class, 'team', 'WITH', 'team.teamId = :adminUserSlackTeam')
               ->andWhere('t.user MEMBER OF team.user')
               ->andWhere('t.user <> :adminUser')
               ->setParameter('adminUserSlackTeam', $slackTeamId)
               ->setParameter('adminUser', $user);
        }

        if ($slackUserToReport) {
            $qb->andWhere('t.user = :user')
               ->setParameter('user', $slackUserToReport->getId());
        }

        return $qb->orderBy('t.dateStart', 'ASC')
                  ->getQuery()
                  ->getResult();
    }


    /**
     * @param \App\Entity\User      $user
     *
     * @return \App\Entity\Timer|null
     */
    public function findNonPunchTimers(User $user)
    {
        return $this->createQueryBuilder('t')
                    ->andWhere('t.user = :user')
                    ->andWhere('t.timerType <> :timerType')
                    ->andWhere('t.dateEnd is NULL')
                    ->orderBy('t.dateStart', 'DESC')
                    ->setParameter('user', $user)
                    ->setParameter('timerType', TimerType::PUNCH)
                    ->getQuery()
                    ->getResult()
            ;
    }

    /**
     * @param \App\Entity\User $user
     * @param \DateTime        $startDayTime
     *
     * @return Timer|null
     */
    public function findPunchTimer(User $user)
    {
        $startDayTime = $this->dateTimeProvider->getLocalUserTime($user);
        $startDayTime->setTime(0,0,0);
        $endDayTime = clone $startDayTime;
        $endDayTime->setTime(23,59,59);

        return $this->createQueryBuilder('t')
                    ->andWhere('t.user = :user')
                    ->andWhere('t.timerType = :timerType')
                    ->andWhere('t.dateStart between :dayStart and :dayEnd')
                    ->orderBy('t.dateStart', 'DESC')
                    ->setParameter('user', $user)
                    ->setParameter('timerType', TimerType::PUNCH)
                    ->setParameter('dayStart', $startDayTime)
                    ->setParameter('dayEnd', $endDayTime)
                    ->getQuery()
                    ->getOneOrNullResult()
            ;
    }

    /**
     * @param \App\Entity\User      $user
     * @param \App\Entity\TimerType $timerType
     *
     * @return \App\Entity\Timer|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findLatestRunningTimerByType(User $user, string $timerType): ?Timer
    {
        $data = $this->createQueryBuilder('t')
                     ->andWhere('t.user = :user')
                     ->andWhere('t.dateEnd is NULL')
                     ->andWhere('t.timerType = :timerType')
                     ->orderBy('t.dateStart', 'DESC')
                     ->setParameter('user', $user)
                     ->setParameter('timerType', $timerType)
                     ->getQuery()
                     ->setMaxResults(1)
                     ->getOneOrNullResult()
        ;

        return $data;
    }
}
