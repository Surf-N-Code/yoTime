<?php

namespace App\Repository;

use App\Entity\SlackTeam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SlackTeam|null find($id, $lockMode = null, $lockVersion = null)
 * @method SlackTeam|null findOneBy(array $criteria, array $orderBy = null)
 * @method SlackTeam[]    findAll()
 * @method SlackTeam[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SlackTeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SlackTeam::class);
    }
}
