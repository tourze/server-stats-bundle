<?php

namespace ServerStatsBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Service\NodeMonitorService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(NodeMonitorService::class)]
#[RunTestsInSeparateProcesses]
final class NodeMonitorServiceTest extends AbstractIntegrationTestCase
{
    private NodeMonitorService $nodeMonitorService;

    protected function getNodeMonitorService(): NodeMonitorService
    {
        if (!isset($this->nodeMonitorService)) {
            $this->nodeMonitorService = self::getService(NodeMonitorService::class);
        }

        return $this->nodeMonitorService;
    }

    protected function onSetUp(): void
    {
        // 集成测试设置，可以在这里进行测试前的初始化
    }

    public function testServiceExists(): void
    {
        // 测试服务类存在且可实例化
        $this->assertInstanceOf(NodeMonitorService::class, $this->getNodeMonitorService());
    }

    public function testGetNetworkMonitorDataReturnsExpectedStructure(): void
    {
        // 创建并持久化一个 Node 实例
        $node = new Node();
        $node->setName('test-node');
        $node->setSshHost('192.168.1.100');
        self::getEntityManager()->persist($node);
        self::getEntityManager()->flush();

        // 执行被测试的方法
        $result = $this->getNodeMonitorService()->getNetworkMonitorData($node);

        // 测试返回结构
        $this->assertArrayHasKey('labels24h', $result);
        $this->assertArrayHasKey('rxData24h', $result);
        $this->assertArrayHasKey('txData24h', $result);
        $this->assertArrayHasKey('labels7d', $result);
        $this->assertArrayHasKey('rxData7d', $result);
        $this->assertArrayHasKey('txData7d', $result);

        // 验证数据结构
        $this->assertIsArray($result['labels24h']);
        $this->assertIsArray($result['rxData24h']);
        $this->assertIsArray($result['txData24h']);
        $this->assertIsArray($result['labels7d']);
        $this->assertIsArray($result['rxData7d']);
        $this->assertIsArray($result['txData7d']);
    }

    public function testGetLoadMonitorDataReturnsExpectedStructure(): void
    {
        // 创建并持久化一个 Node 实例
        $node = new Node();
        $node->setName('test-node');
        $node->setSshHost('192.168.1.100');
        self::getEntityManager()->persist($node);
        self::getEntityManager()->flush();

        // 执行被测试的方法
        $result = $this->getNodeMonitorService()->getLoadMonitorData($node);

        // 测试返回结构
        $this->assertArrayHasKey('labels', $result);
        $this->assertArrayHasKey('cpuUserData', $result);
        $this->assertArrayHasKey('cpuSystemData', $result);
        $this->assertArrayHasKey('cpuIdleData', $result);
        $this->assertArrayHasKey('loadOneData', $result);
        $this->assertArrayHasKey('memoryTotalData', $result);
        $this->assertArrayHasKey('processRunningData', $result);

        // 验证数据结构
        $this->assertIsArray($result['labels']);
        $this->assertIsArray($result['cpuUserData']);
        $this->assertIsArray($result['cpuSystemData']);
        $this->assertIsArray($result['cpuIdleData']);
        $this->assertIsArray($result['loadOneData']);
        $this->assertIsArray($result['memoryTotalData']);
        $this->assertIsArray($result['processRunningData']);
    }
}
