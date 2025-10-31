<?php

namespace ServerStatsBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use ServerNodeBundle\DataFixtures\NodeFixtures;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Entity\DailyTraffic;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * 日流量数据填充
 * 生成测试用的日流量统计数据
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class DailyTrafficFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const DAILY_TRAFFIC_PREFIX = 'daily-traffic-';

    public static function getGroups(): array
    {
        return ['test', 'dev'];
    }

    public function load(ObjectManager $manager): void
    {
        // IP地址列表
        $ipAddresses = [
            '192.168.1.10',
            '10.0.0.15',
            '172.16.0.5',
            '203.0.113.100',
            '198.51.100.200',
        ];

        // 获取可用的节点引用
        $nodeRefs = [
            NodeFixtures::REFERENCE_NODE_1,
            NodeFixtures::REFERENCE_NODE_2,
        ];

        $refIndex = 0;

        // 为每个节点生成过去15天的数据
        foreach ($nodeRefs as $nodeRef) {
            $node = $this->getReference($nodeRef, Node::class);
            assert($node instanceof Node);

            for ($i = 0; $i < 15; ++$i) {
                $date = new \DateTimeImmutable("-{$i} days");
                $ip = $ipAddresses[$i % count($ipAddresses)];

                $dailyTraffic = new DailyTraffic();
                $dailyTraffic->setNode($node);
                $dailyTraffic->setIp($ip);
                $dailyTraffic->setDate($date);

                // 生成随机流量数据 (字节)
                $baseTx = rand(1000000, 100000000); // 1MB-100MB
                $baseRx = rand(5000000, 500000000); // 5MB-500MB

                $dailyTraffic->setTx((string) $baseTx);
                $dailyTraffic->setRx((string) $baseRx);

                $manager->persist($dailyTraffic);

                // 创建引用
                $this->addReference(self::DAILY_TRAFFIC_PREFIX . $refIndex, $dailyTraffic);
                ++$refIndex;
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            NodeFixtures::class,
        ];
    }
}
