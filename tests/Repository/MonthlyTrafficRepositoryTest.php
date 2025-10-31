<?php

namespace ServerStatsBundle\Tests\Repository;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Entity\MonthlyTraffic;
use ServerStatsBundle\Repository\MonthlyTrafficRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(MonthlyTrafficRepository::class)]
#[RunTestsInSeparateProcesses]
final class MonthlyTrafficRepositoryTest extends AbstractRepositoryTestCase
{
    private MonthlyTrafficRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(MonthlyTrafficRepository::class);
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

        $entity = new MonthlyTraffic();
        $entity->setNode($node);
        $entity->setMonth(CarbonImmutable::now()->addMonths(rand(1, 12))->format('Y-m'));
        $entity->setIp('127.0.0.1');
        $entity->setRx('5000');
        $entity->setTx('8000');

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<MonthlyTraffic>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
