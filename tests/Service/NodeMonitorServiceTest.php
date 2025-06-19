<?php

namespace ServerStatsBundle\Tests\Service;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Repository\MinuteStatRepository;
use ServerStatsBundle\Service\NodeMonitorService;

class NodeMonitorServiceTest extends TestCase
{
    /**
     * @var MinuteStatRepository&\PHPUnit\Framework\MockObject\MockObject
     */
    private $minuteStatRepository;
    
    private NodeMonitorService $nodeMonitorService;
    
    protected function setUp(): void
    {
        $this->minuteStatRepository = $this->createMock(MinuteStatRepository::class);
        $this->nodeMonitorService = new NodeMonitorService($this->minuteStatRepository);
    }
    
    public function testGetNetworkMonitorData(): void
    {
        $node = new Node();
        
        // 模拟查询构建器行为而不详细测试其方法
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);
        $mockQuery = $this->createMock(Query::class);
        
        // 使用简化的期望配置
        $mockQueryBuilder->method('where')->willReturnSelf();
        $mockQueryBuilder->method('andWhere')->willReturnSelf();
        $mockQueryBuilder->method('setParameter')->willReturnSelf();
        $mockQueryBuilder->method('orderBy')->willReturnSelf();
        $mockQueryBuilder->method('getQuery')->willReturn($mockQuery);
        
        // 为简单起见，假设查询返回空结果
        $mockQuery->method('getResult')->willReturn([]);
        
        // 设置存储库的期望行为
        $this->minuteStatRepository->method('createQueryBuilder')
            ->willReturn($mockQueryBuilder);
        
        // 执行被测试的方法
        $result = $this->nodeMonitorService->getNetworkMonitorData($node);
        
        // 只测试返回结构，不测试具体值
        $this->assertArrayHasKey('labels24h', $result);
        $this->assertArrayHasKey('rxData24h', $result);
        $this->assertArrayHasKey('txData24h', $result);
        $this->assertArrayHasKey('labels7d', $result);
        $this->assertArrayHasKey('rxData7d', $result);
        $this->assertArrayHasKey('txData7d', $result);
    }
    
    public function testGetLoadMonitorData(): void
    {
        $node = new Node();
        
        // 模拟查询构建器行为而不详细测试其方法
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);
        $mockQuery = $this->createMock(Query::class);
        
        // 使用简化的期望配置
        $mockQueryBuilder->method('where')->willReturnSelf();
        $mockQueryBuilder->method('andWhere')->willReturnSelf();
        $mockQueryBuilder->method('setParameter')->willReturnSelf();
        $mockQueryBuilder->method('orderBy')->willReturnSelf();
        $mockQueryBuilder->method('getQuery')->willReturn($mockQuery);
        
        // 为简单起见，假设查询返回空结果
        $mockQuery->method('getResult')->willReturn([]);
        
        // 设置存储库的期望行为
        $this->minuteStatRepository->method('createQueryBuilder')
            ->willReturn($mockQueryBuilder);
        
        // 执行被测试的方法
        $result = $this->nodeMonitorService->getLoadMonitorData($node);
        
        // 只测试返回结构，不测试具体值
        $this->assertArrayHasKey('labels', $result);
        $this->assertArrayHasKey('cpuUserData', $result);
        $this->assertArrayHasKey('cpuSystemData', $result);
        $this->assertArrayHasKey('cpuIdleData', $result);
        // 添加其他关键结构检查
        $this->assertArrayHasKey('loadOneData', $result);
        $this->assertArrayHasKey('memoryTotalData', $result);
        $this->assertArrayHasKey('processRunningData', $result);
    }
} 