<?php

namespace App\Repository;

use App\Entity\DailySummary;
use App\Entity\SlackTeam;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DailySummary|null find($id, $lockMode = null, $lockVersion = null)
 * @method DailySummary|null findOneBy(array $criteria, array $orderBy = null)
 * @method DailySummary[]    findAll()
 * @method DailySummary[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DailySummaryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailySummary::class);
    }

    public function findDailySummaryByPeriod(
        \DateTime $userLocalTime,
        string $constraint,
        string $slackUserIdToReport = null,
        string $slackTeamId = null
    )
    {
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

        $qb = $this->createQueryBuilder(SlashCommandHandler::DAILY_SUMMARY);

        switch ($constraint) {
            case 'day':
                $qb->andWhere('dayofyear(ds.date) = dayofyear(:today)')
                   ->setParameter('today', new \DateTime());
                break;
            case 'week':
                $qb->andWhere('ds.date >= :weekStart')
                   ->andWhere('ds.date <= :weekEnd')
                   ->setParameter('weekStart', $weekStart)
                   ->setParameter('weekEnd', $weekEnd);
                break;
            case 'month':
                $qb->andWhere('ds.date >= :monthStart')
                   ->andWhere('ds.date <= :monthEnd')
                   ->setParameter('monthStart', $monthStart)
                   ->setParameter('monthEnd', $monthEnd);
                break;
            case 'year':
                $qb->andWhere('ds.date >= :yearStart')
                   ->andWhere('ds.date <= :yearEnd')
                   ->setParameter('yearStart', $yearStart)
                   ->setParameter('yearEnd', $yearEnd);
                break;
            case 'all':
                break;
        }

        if (!$slackUserIdToReport) {
            $qb->leftJoin(User::class, 'u', 'WITH', 'ds.user = u.id')
               ->leftJoin(SlackTeam::class, 'team', 'WITH', 'team.teamId = :adminUserSlackTeam')
               ->andWhere('ds.user MEMBER OF team.user')
               ->setParameter('adminUserSlackTeam', $slackTeamId);
        }

        if ($slackUserIdToReport) {
            $qb->andWhere('ds.user = :user')
               ->setParameter('user', $slackUserIdToReport->getId());
        }

        return $qb->orderBy('ds.date, ds.user', 'ASC')
                  ->getQuery()
                  ->getResult();
    }
}
