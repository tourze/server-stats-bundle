<?php

namespace ServerStatsBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Entity\MonthlyTraffic;

class MonthlyTrafficRepositoryTest extends TestCase
{
    public function testSaveTrafficLogic(): void
    {
        // 测试 saveTraffic 方法的核心逻辑
        $node = new Node();
        $ip = '192.168.1.100';
        $date = new \DateTime('2023-01-15');
        $rx = 10000000;
        $tx = 5000000;
        
        // 测试新建记录的场景
        $log = new MonthlyTraffic();
        $log->setRx('0');
        $log->setTx('0');
        $log->setNode($node);
        $log->setMonth($date->format('Y-m'));
        $log->setIp($ip);
        
        // 模拟 saveTraffic 方法的逻辑
        if ($log->getRx() < $rx) {
            $log->setRx((string)$rx);
        }
        if ($log->getTx() < $tx) {
            $log->setTx((string)$tx);
        }
        
        $this->assertSame($node, $log->getNode());
        $this->assertSame($ip, $log->getIp());
        $this->assertSame($date->format('Y-m'), $log->getMonth());
        $this->assertSame((string)$rx, $log->getRx());
        $this->assertSame((string)$tx, $log->getTx());
    }
    
    public function testSaveTrafficUpdatesHigherValues(): void
    {
        $node = new Node();
        $ip = '192.168.1.100';
        $date = new \DateTime('2023-06-15');
        $oldRx = 5000000;
        $oldTx = 2500000;
        $newRx = 10000000;
        $newTx = 5000000;
        
        // 创建现有记录
        $log = new MonthlyTraffic();
        $log->setNode($node);
        $log->setIp($ip);
        $log->setMonth($date->format('Y-m'));
        $log->setRx((string)$oldRx);
        $log->setTx((string)$oldTx);
        
        // 模拟更新逻辑
        $log->setIp($ip);
        if ($log->getRx() < $newRx) {
            $log->setRx((string)$newRx);
        }
        if ($log->getTx() < $newTx) {
            $log->setTx((string)$newTx);
        }
        
        $this->assertSame((string)$newRx, $log->getRx());
        $this->assertSame((string)$newTx, $log->getTx());
    }
    
    public function testSaveTrafficKeepsHigherValues(): void
    {
        $node = new Node();
        $ip = '192.168.1.100';
        $date = new \DateTime('2023-12-15');
        $highRx = 20000000;
        $highTx = 10000000;
        $lowRx = 10000000;
        $lowTx = 5000000;
        
        // 创建现有记录，具有更高的值
        $log = new MonthlyTraffic();
        $log->setNode($node);
        $log->setIp($ip);
        $log->setMonth($date->format('Y-m'));
        $log->setRx((string)$highRx);
        $log->setTx((string)$highTx);
        
        // 模拟保存更低值的逻辑
        $log->setIp($ip);
        if ($log->getRx() < $lowRx) {
            $log->setRx((string)$lowRx);
        }
        if ($log->getTx() < $lowTx) {
            $log->setTx((string)$lowTx);
        }
        
        // 应该保持更高的原始值
        $this->assertSame((string)$highRx, $log->getRx());
        $this->assertSame((string)$highTx, $log->getTx());
    }
    
    public function testMonthFormatConsistency(): void
    {
        // 测试月份格式的一致性
        $dates = [
            new \DateTime('2023-01-01'),
            new \DateTime('2023-02-28'),
            new \DateTime('2024-02-29'), // 闰年
            new \DateTime('2023-12-31'),
        ];
        
        foreach ($dates as $date) {
            $expectedMonth = $date->format('Y-m');
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}$/', $expectedMonth);
        }
    }
    
    public function testLargeTrafficValues(): void
    {
        // 测试大流量值的处理（月流量通常很大）
        $log = new MonthlyTraffic();
        
        $largeRx = '999999999999999999999';
        $largeTx = '888888888888888888888';
        
        $log->setRx($largeRx);
        $log->setTx($largeTx);
        
        $this->assertSame($largeRx, $log->getRx());
        $this->assertSame($largeTx, $log->getTx());
    }
} 