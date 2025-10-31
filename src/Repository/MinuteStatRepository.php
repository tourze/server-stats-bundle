<?php

namespace ServerStatsBundle\Repository;

use Carbon\CarbonInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Entity\MinuteStat;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<MinuteStat>
 */
#[AsRepository(entityClass: MinuteStat::class)]
class MinuteStatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MinuteStat::class);
    }

    public function save(MinuteStat $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MinuteStat $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByNodeAndTime(Node $node, CarbonInterface $datetime): ?MinuteStat
    {
        return $this->findOneBy([
            'node' => $node,
            'datetime' => $datetime->clone()->startOfMinute(),
        ]);
    }
}
