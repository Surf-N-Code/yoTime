<?php


namespace App\Doctrine;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\DailySummary;
use App\Entity\Task;
use App\Entity\Timer;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class UserDataFilterQueryExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private $security;
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }
    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if ($resourceClass !== Timer::class &&
            $resourceClass !== DailySummary::class &&
            $resourceClass !== Task::class &&
            $resourceClass !== User::class
        ) {
            return;
        }
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }
        if (!$this->security->getUser()) {
            return;
        }
        $rootAlias = $queryBuilder->getRootAliases()[0];

        if ($resourceClass === User::class) {
            $queryBuilder->andWhere(sprintf('%s.email = :email', $rootAlias))
                         ->setParameter('email', $this->security->getUser()->getUsername());
        } else {
            $queryBuilder->andWhere(sprintf('%s.user = :user', $rootAlias))
                         ->setParameter('user', $this->security->getUser());
        }
    }
}
