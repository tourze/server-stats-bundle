<?php

namespace ServerStatsBundle\Tests\Repository;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Entity\MinuteStat;
use ServerStatsBundle\Repository\MinuteStatRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(MinuteStatRepository::class)]
#[RunTestsInSeparateProcesses]
final class MinuteStatRepositoryTest extends AbstractRepositoryTestCase
{
    private MinuteStatRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(MinuteStatRepository::class);
    }

    protected function createNewEntity(): MinuteStat
    {
        $node = new Node();
        $node->setName('Test Node ' . uniqid());
        $node->setApiKey('test-key-' . uniqid());
        $node->setApiSecret('test-secret-' . uniqid());
        $node->setSshHost('127.0.0.1');
        self::getEntityManager()->persist($node);
        self::getEntityManager()->flush();

        $entity = new MinuteStat();
        $entity->setNode($node);
        $entity->setDatetime(CarbonImmutable::now()->addMinutes(rand(1, 1440))->startOfMinute());
        $entity->setMemoryTotal(8192);
        $entity->setMemoryUsed(4096);
        $entity->setMemoryFree(4096);
        $entity->setRxBandwidth('1000');
        $entity->setTxBandwidth('2000');

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<MinuteStat>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    public function testFindByNodeAndTimeFoundRecord(): void
    {
        // 创建并保存一个测试实体
        $entity = $this->createNewEntity();
        self::getEntityManager()->persist($entity);
        self::getEntityManager()->flush();

        // 使用同样的node和时间查找
        $entityDatetime = $entity->getDatetime();
        self::assertNotNull($entityDatetime);
        self::assertInstanceOf(CarbonImmutable::class, $entityDatetime);
        $found = $this->repository->findByNodeAndTime($entity->getNode(), $entityDatetime);

        self::assertNotNull($found);
        self::assertSame($entity->getId(), $found->getId());
        self::assertEquals($entity->getNode()->getId(), $found->getNode()->getId());
        $foundEntityDatetime = $found->getDatetime();
        self::assertNotNull($foundEntityDatetime);
        self::assertInstanceOf(CarbonImmutable::class, $foundEntityDatetime);
        self::assertEquals($entityDatetime->startOfMinute(), $foundEntityDatetime);
    }

    public function testFindByNodeAndTimeNotFound(): void
    {
        // 创建一个测试Node（但不保存MinuteStat）
        $node = new Node();
        $node->setName('Test Node ' . uniqid());
        $node->setApiKey('test-key-' . uniqid());
        $node->setApiSecret('test-secret-' . uniqid());
        $node->setSshHost('127.0.0.1');
        self::getEntityManager()->persist($node);
        self::getEntityManager()->flush();

        // 查找不存在的记录
        $found = $this->repository->findByNodeAndTime($node, CarbonImmutable::now());

        self::assertNull($found);
    }

    public function testFindByNodeAndTimeNormalizesToMinuteStart(): void
    {
        // 创建一个带有精确时间的实体（包含秒和毫秒）
        $entity = $this->createNewEntity();
        $exactTime = CarbonImmutable::now()->addMinutes(30)->setSeconds(45)->setMicrosecond(123456);
        $entity->setDatetime($exactTime->startOfMinute()); // Repository会normalize到分钟开始
        self::getEntityManager()->persist($entity);
        self::getEntityManager()->flush();

        // 使用非normalized的时间查找（应该能找到，因为repository内部会normalize）
        $queryTime = $exactTime; // 包含秒和毫秒
        $found = $this->repository->findByNodeAndTime($entity->getNode(), $queryTime);

        self::assertNotNull($found);
        self::assertSame($entity->getId(), $found->getId());

        // 验证返回的时间是normalized的（startOfMinute）
        $foundDatetime = $found->getDatetime();
        self::assertNotNull($foundDatetime);
        self::assertEquals($exactTime->startOfMinute(), $foundDatetime);

        // 验证秒和微秒被重置为0（只对Carbon对象进行验证）
        if ($foundDatetime instanceof CarbonImmutable) {
            self::assertEquals(0, $foundDatetime->second);
            self::assertEquals(0, $foundDatetime->microsecond);
        }
    }
}
