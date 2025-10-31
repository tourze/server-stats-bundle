<?php

namespace ServerStatsBundle\Tests\Repository;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Entity\DailyTraffic;
use ServerStatsBundle\Repository\DailyTrafficRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(DailyTrafficRepository::class)]
#[RunTestsInSeparateProcesses]
final class DailyTrafficRepositoryTest extends AbstractRepositoryTestCase
{
    private DailyTrafficRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(DailyTrafficRepository::class);
    }

    protected function createNewEntity(): object
    {
        $node = new Node();
        $node->setName('Test Node ' . uniqid());
        $node->setApiKey('test-key-' . uniqid());
        $node->setApiSecret('test-secret-' . uniqid());
        $node->setSshHost('127.0.0.1');
        self::getEntityManager()->persist($node);
        self::getEntityManager()->flush();

        $entity = new DailyTraffic();
        $entity->setNode($node);
        $entity->setDate(CarbonImmutable::now()->addDays(rand(1, 365))->startOfDay());
        $entity->setIp('127.0.0.1');
        $entity->setRx('1000');
        $entity->setTx('2000');

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<DailyTraffic>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
