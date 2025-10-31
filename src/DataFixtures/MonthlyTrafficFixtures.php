<?php

namespace ServerStatsBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use ServerNodeBundle\DataFixtures\NodeFixtures;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Entity\MonthlyTraffic;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * 月流量数据填充
 * 生成测试用的月度流量统计数据
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class MonthlyTrafficFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const MONTHLY_TRAFFIC_PREFIX = 'monthly-traffic-';

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

        // 为每个节点生成过去6个月的数据
        foreach ($nodeRefs as $nodeRef) {
            $node = $this->getReference($nodeRef, Node::class);
            assert($node instanceof Node);

            for ($i = 0; $i < 6; ++$i) {
                $date = new \DateTime("-{$i} months");
                $month = $date->format('Y-m'); // YYYY-MM格式
                $ip = $ipAddresses[$i % count($ipAddresses)];

                $monthlyTraffic = new MonthlyTraffic();
                $monthlyTraffic->setNode($node);
                $monthlyTraffic->setIp($ip);
                $monthlyTraffic->setMonth($month);

                // 生成随机流量数据 (字节) - 月度数据比日度数据更大
                $baseTx = rand(100000000, 10000000000); // 100MB-10GB
                $baseRx = rand(500000000, 50000000000); // 500MB-50GB

                $monthlyTraffic->setTx((string) $baseTx);
                $monthlyTraffic->setRx((string) $baseRx);

                $manager->persist($monthlyTraffic);

                // 创建引用
                $this->addReference(self::MONTHLY_TRAFFIC_PREFIX . $refIndex, $monthlyTraffic);
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
