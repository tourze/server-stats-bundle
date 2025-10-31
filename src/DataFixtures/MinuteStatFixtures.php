<?php

namespace ServerStatsBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use ServerNodeBundle\DataFixtures\NodeFixtures;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Entity\MinuteStat;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * 分钟统计数据填充
 * 生成测试用的系统监控统计数据
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class MinuteStatFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const MINUTE_STAT_PREFIX = 'minute-stat-';

    public static function getGroups(): array
    {
        return ['test', 'dev'];
    }

    public function load(ObjectManager $manager): void
    {
        // 获取可用的节点引用
        $nodeRefs = [
            NodeFixtures::REFERENCE_NODE_1,
            NodeFixtures::REFERENCE_NODE_2,
        ];

        // 生成过去24小时的数据（每5分钟一个数据点）
        $totalMinutes = 24 * 60;
        $interval = 5; // 每5分钟
        $dataPoints = $totalMinutes / $interval;

        for ($i = 0; $i < $dataPoints; ++$i) {
            $minutesAgo = $i * $interval;
            $datetime = new \DateTimeImmutable("-{$minutesAgo} minutes");

            foreach ($nodeRefs as $index => $nodeRef) {
                $node = $this->getReference($nodeRef, Node::class);
                assert($node instanceof Node);

                $minuteStat = new MinuteStat();
                $minuteStat->setNode($node);
                $minuteStat->setDatetime($datetime);

                // CPU 统计 (总和应该接近100%)
                $cpuIdle = rand(40, 80);
                $cpuUser = rand(10, 30);
                $cpuSystem = rand(5, 20);
                $cpuRemaining = 100 - $cpuIdle - $cpuUser - $cpuSystem;
                $cpuStolen = max(0, min($cpuRemaining, rand(0, 5)));

                $minuteStat->setCpuIdlePercent($cpuIdle);
                $minuteStat->setCpuUserPercent($cpuUser);
                $minuteStat->setCpuSystemPercent($cpuSystem);
                $minuteStat->setCpuStolenPercent($cpuStolen);

                // 负载统计
                $baseLoad = rand(50, 200) / 100; // 0.5-2.0
                $minuteStat->setLoadOneMinute(sprintf('%.2f', $baseLoad));
                $minuteStat->setLoadFiveMinutes(sprintf('%.2f', $baseLoad + rand(-20, 20) / 100));
                $minuteStat->setLoadFifteenMinutes(sprintf('%.2f', $baseLoad + rand(-30, 30) / 100));

                // 进程统计
                $totalProcesses = rand(150, 300);
                $runningProcesses = rand(1, 10);
                $minuteStat->setProcessTotal($totalProcesses);
                $minuteStat->setProcessRunning($runningProcesses);
                $minuteStat->setProcessUninterruptibleSleep(rand(0, 2));
                $minuteStat->setProcessWaitingForRun(rand(0, 5));

                // 内存统计 (MB)
                $totalMemory = rand(4000, 32000); // 4GB-32GB
                $usedMemory = rand(1000, (int) ($totalMemory * 0.8));
                $freeMemory = $totalMemory - $usedMemory;
                $availableMemory = $freeMemory + rand(500, 2000);

                $minuteStat->setMemoryTotal($totalMemory);
                $minuteStat->setMemoryUsed($usedMemory);
                $minuteStat->setMemoryFree($freeMemory);
                $minuteStat->setMemoryAvailable($availableMemory);
                $minuteStat->setMemoryBuffer(rand(100, 500));
                $minuteStat->setMemoryCache(rand(500, 2000));
                $minuteStat->setMemoryShared(rand(50, 200));
                $minuteStat->setMemorySwapUsed(rand(0, 1000));

                // 网络统计
                $minuteStat->setRxBandwidth((string) rand(1000000, 100000000)); // 1MB-100MB
                $minuteStat->setRxPackets((string) rand(1000, 100000));
                $minuteStat->setTxBandwidth((string) rand(500000, 50000000)); // 0.5MB-50MB
                $minuteStat->setTxPackets((string) rand(500, 50000));

                // 磁盘IO统计
                $minuteStat->setDiskReadIops(sprintf('%.2f', rand(10, 1000)));
                $minuteStat->setDiskWriteIops(sprintf('%.2f', rand(5, 500)));
                $minuteStat->setDiskIoWait(sprintf('%.2f', rand(0, 50)));
                $minuteStat->setDiskAvgIoTime(sprintf('%.2f', rand(1, 20)));
                $minuteStat->setDiskBusyPercent(sprintf('%.2f', rand(5, 80)));

                // TCP/UDP 连接统计
                $minuteStat->setTcpEstab(rand(50, 500));
                $minuteStat->setTcpListen(rand(5, 50));
                $minuteStat->setTcpSynSent(rand(0, 10));
                $minuteStat->setTcpSynRecv(rand(0, 5));
                $minuteStat->setTcpFinWait1(rand(0, 20));
                $minuteStat->setTcpFinWait2(rand(0, 15));
                $minuteStat->setTcpTimeWait(rand(0, 100));
                $minuteStat->setTcpCloseWait(rand(0, 10));
                $minuteStat->setTcpClosing(rand(0, 5));
                $minuteStat->setTcpLastAck(rand(0, 5));
                $minuteStat->setUdpCount(rand(5, 50));

                // 在线用户数据
                $onlineUsers = [];
                $userCount = rand(0, 10);
                for ($j = 0; $j < $userCount; ++$j) {
                    $onlineUsers[] = [
                        'user' => 'user' . rand(1000, 9999),
                        'terminal' => 'pts/' . $j,
                        'login_time' => $datetime->modify('-' . rand(1, 300) . ' minutes')->format('Y-m-d H:i:s'),
                    ];
                }
                $minuteStat->setOnlineUsers($onlineUsers);

                $manager->persist($minuteStat);

                // 创建引用
                if (0 === $index) {
                    $this->addReference(self::MINUTE_STAT_PREFIX . $i, $minuteStat);
                }
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
