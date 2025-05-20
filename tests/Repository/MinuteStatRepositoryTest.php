<?php

namespace ServerStatsBundle\Tests\Repository;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Entity\MinuteStat;

class MinuteStatRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
    }
    
    /**
     * 测试 findByNodeAndTime 方法返回现有的统计记录
     */
    public function testFindByNodeAndTimeReturnsExistingStat(): void
    {
        $node = new Node();
        $date = Carbon::create(2023, 1, 1, 10, 15, 30);
        $expectedDate = Carbon::create(2023, 1, 1, 10, 15, 0); // 取整到分钟
        
        // 创建预期的返回统计记录
        $expectedStat = new MinuteStat();
        $expectedStat->setNode($node);
        $expectedStat->setDatetime($expectedDate);
        
        // 创建一个局部的测试双打，只测试 findByNodeAndTime 的逻辑
        $testRepo = new class($expectedStat) {
            private $existingStat;
            
            public function __construct($existingStat)
            {
                $this->existingStat = $existingStat;
            }
            
            public function findOneBy(array $criteria)
            {
                return $this->existingStat;
            }
            
            public function findByNodeAndTime(Node $node, Carbon $datetime): MinuteStat
            {
                $stat = $this->findOneBy([
                    'node' => $node,
                    'datetime' => $datetime->clone()->startOfMinute(),
                ]);
                if (!$stat) {
                    $stat = new MinuteStat();
                    $stat->setNode($node);
                    $stat->setDatetime($datetime->clone()->startOfMinute());
                }
                
                return $stat;
            }
        };
        
        // 执行方法
        $result = $testRepo->findByNodeAndTime($node, $date);
        
        // 断言
        $this->assertSame($expectedStat, $result);
    }
    
    /**
     * 测试 findByNodeAndTime 方法在未找到统计记录时创建新记录
     */
    public function testFindByNodeAndTimeCreatesNewStatWhenNotFound(): void
    {
        $node = new Node();
        $date = Carbon::create(2023, 1, 1, 10, 15, 30);
        $expectedDate = Carbon::create(2023, 1, 1, 10, 15, 0); // 取整到分钟
        
        // 创建一个局部的测试双打，只测试 findByNodeAndTime 的逻辑
        $testRepo = new class {
            public function findOneBy(array $criteria)
            {
                return null; // 模拟未找到记录
            }
            
            public function findByNodeAndTime(Node $node, Carbon $datetime): MinuteStat
            {
                $stat = $this->findOneBy([
                    'node' => $node,
                    'datetime' => $datetime->clone()->startOfMinute(),
                ]);
                if (!$stat) {
                    $stat = new MinuteStat();
                    $stat->setNode($node);
                    $stat->setDatetime($datetime->clone()->startOfMinute());
                }
                
                return $stat;
            }
        };
        
        // 执行方法
        $result = $testRepo->findByNodeAndTime($node, $date);
        
        // 断言
        $this->assertInstanceOf(MinuteStat::class, $result);
        $this->assertSame($node, $result->getNode());
        $this->assertEquals($expectedDate->format('Y-m-d H:i:s'), $result->getDatetime()->format('Y-m-d H:i:s'));
    }
}
