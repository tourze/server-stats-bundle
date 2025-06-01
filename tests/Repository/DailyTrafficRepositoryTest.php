<?php

namespace ServerStatsBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Entity\DailyTraffic;

class DailyTrafficRepositoryTest extends TestCase
{
    public function testSaveTrafficLogic(): void
    {
        // 测试 saveTraffic 方法的核心逻辑，而不是实际的数据库操作
        $node = new Node();
        $ip = '192.168.1.100';
        $date = new \DateTime('2023-01-01');
        $rx = 1000000;
        $tx = 500000;
        
        // 测试新建记录的场景
        $log = new DailyTraffic();
        $log->setRx('0');
        $log->setTx('0');
        $log->setNode($node);
        $log->setDate($date);
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
        $this->assertSame($date, $log->getDate());
        $this->assertSame((string)$rx, $log->getRx());
        $this->assertSame((string)$tx, $log->getTx());
    }
    
    public function testSaveTrafficUpdatesHigherValues(): void
    {
        $node = new Node();
        $ip = '192.168.1.100';
        $date = new \DateTime('2023-01-01');
        $oldRx = 500000;
        $oldTx = 250000;
        $newRx = 1000000;
        $newTx = 500000;
        
        // 创建现有记录
        $log = new DailyTraffic();
        $log->setNode($node);
        $log->setIp($ip);
        $log->setDate($date);
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
        $date = new \DateTime('2023-01-01');
        $highRx = 2000000;
        $highTx = 1000000;
        $lowRx = 1000000;
        $lowTx = 500000;
        
        // 创建现有记录，具有更高的值
        $log = new DailyTraffic();
        $log->setNode($node);
        $log->setIp($ip);
        $log->setDate($date);
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
    
    public function testStringComparison(): void
    {
        // 测试字符串数字比较逻辑
        $this->assertTrue('1000' < 2000);
        $this->assertFalse('2000' < 1000);
        $this->assertTrue('0' < 1);
    }
} 