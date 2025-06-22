<?php

namespace ServerStatsBundle\Repository;

use Carbon\CarbonInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Entity\MinuteStat;

/**
 * @method MinuteStat|null find($id, $lockMode = null, $lockVersion = null)
 * @method MinuteStat|null findOneBy(array $criteria, array $orderBy = null)
 * @method MinuteStat[] findAll()
 * @method MinuteStat[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MinuteStatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MinuteStat::class);
    }

    public function findByNodeAndTime(Node $node, CarbonInterface $datetime): MinuteStat
    {
        $stat = $this->findOneBy([
            'node' => $node,
            'datetime' => $datetime->clone()->startOfMinute(),
        ]);
        if ($stat === null) {
            $stat = new MinuteStat();
            $stat->setNode($node);
            $stat->setDatetime($datetime->clone()->startOfMinute());
        }

        return $stat;
    }
}
