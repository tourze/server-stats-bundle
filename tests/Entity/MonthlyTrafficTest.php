<?php

namespace ServerStatsBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Entity\MonthlyTraffic;

class MonthlyTrafficTest extends TestCase
{
    private MonthlyTraffic $monthlyTraffic;
    
    protected function setUp(): void
    {
        $this->monthlyTraffic = new MonthlyTraffic();
    }
    
    public function testIdInitiallyNull(): void
    {
        $this->assertNull($this->monthlyTraffic->getId());
    }
    
    public function testNodeGetterAndSetter(): void
    {
        $node = new Node();
        
        $this->monthlyTraffic->setNode($node);
        $this->assertSame($node, $this->monthlyTraffic->getNode());
        
        // 测试设置为 null
        $this->monthlyTraffic->setNode(null);
        $this->assertNull($this->monthlyTraffic->getNode());
    }
    
    public function testIpGetterAndSetter(): void
    {
        $ip = '192.168.1.100';
        
        $result = $this->monthlyTraffic->setIp($ip);
        $this->assertSame($ip, $this->monthlyTraffic->getIp());
        $this->assertSame($this->monthlyTraffic, $result); // 测试链式调用
    }
    
    public function testIpWithDifferentFormats(): void
    {
        // IPv4
        $ipv4 = '10.0.0.1';
        $this->monthlyTraffic->setIp($ipv4);
        $this->assertSame($ipv4, $this->monthlyTraffic->getIp());
        
        // IPv6
        $ipv6 = '::1';
        $this->monthlyTraffic->setIp($ipv6);
        $this->assertSame($ipv6, $this->monthlyTraffic->getIp());
        
        // 边界测试 - 长IP地址
        $longIp = '255.255.255.255';
        $this->monthlyTraffic->setIp($longIp);
        $this->assertSame($longIp, $this->monthlyTraffic->getIp());
    }
    
    public function testMonthGetterAndSetter(): void
    {
        $month = '2023-01';
        
        $result = $this->monthlyTraffic->setMonth($month);
        $this->assertSame($month, $this->monthlyTraffic->getMonth());
        $this->assertSame($this->monthlyTraffic, $result); // 测试链式调用
    }
    
    public function testMonthWithDifferentFormats(): void
    {
        // 测试不同年月格式
        $months = [
            '2023-01',
            '2023-12',
            '2024-02',
            '2025-06',
        ];
        
        foreach ($months as $month) {
            $this->monthlyTraffic->setMonth($month);
            $this->assertSame($month, $this->monthlyTraffic->getMonth());
        }
    }
    
    public function testTxGetterAndSetter(): void
    {
        $tx = '10240000';
        
        $result = $this->monthlyTraffic->setTx($tx);
        $this->assertSame($tx, $this->monthlyTraffic->getTx());
        $this->assertSame($this->monthlyTraffic, $result); // 测试链式调用
        
        // 测试零值
        $this->monthlyTraffic->setTx('0');
        $this->assertSame('0', $this->monthlyTraffic->getTx());
    }
    
    public function testTxWithLargeValues(): void
    {
        // 测试超大数值（月流量通常比日流量大）
        $largeTx = '99999999999999999999';
        $this->monthlyTraffic->setTx($largeTx);
        $this->assertSame($largeTx, $this->monthlyTraffic->getTx());
        
        // 测试零值
        $this->monthlyTraffic->setTx('0');
        $this->assertSame('0', $this->monthlyTraffic->getTx());
    }
    
    public function testRxGetterAndSetter(): void
    {
        $rx = '20480000';
        
        $result = $this->monthlyTraffic->setRx($rx);
        $this->assertSame($rx, $this->monthlyTraffic->getRx());
        $this->assertSame($this->monthlyTraffic, $result); // 测试链式调用
        
        // 测试零值
        $this->monthlyTraffic->setRx('0');
        $this->assertSame('0', $this->monthlyTraffic->getRx());
    }
    
    public function testRxWithLargeValues(): void
    {
        // 测试超大数值
        $largeRx = '88888888888888888888';
        $this->monthlyTraffic->setRx($largeRx);
        $this->assertSame($largeRx, $this->monthlyTraffic->getRx());
        
        // 测试零值
        $this->monthlyTraffic->setRx('0');
        $this->assertSame('0', $this->monthlyTraffic->getRx());
    }
    
    public function testToStringWithCompleteData(): void
    {
        $node = new Node();
        $this->monthlyTraffic->setNode($node);
        $this->monthlyTraffic->setIp('192.168.1.100');
        $this->monthlyTraffic->setMonth('2023-06');
        
        $expectedPattern = '/MonthlyTraffic\[\] 192\.168\.1\.100 - 2023-06/';
        $this->assertMatchesRegularExpression($expectedPattern, (string)$this->monthlyTraffic);
    }
    
    public function testToStringWithMissingData(): void
    {
        // 只设置部分数据
        $this->monthlyTraffic->setIp('127.0.0.1');
        $this->monthlyTraffic->setMonth('2023-12');
        
        $result = (string)$this->monthlyTraffic;
        $this->assertStringContainsString('127.0.0.1', $result);
        $this->assertStringContainsString('2023-12', $result);
    }
    
    public function testCompleteWorkflow(): void
    {
        // 模拟完整的数据设置流程
        $node = new Node();
        $ip = '172.16.0.1';
        $month = '2023-08';
        $tx = '50000000';
        $rx = '80000000';
        
        // setNode 返回 void，不支持链式调用
        $this->monthlyTraffic->setNode($node);
        
        $result = $this->monthlyTraffic
            ->setIp($ip)
            ->setMonth($month)
            ->setTx($tx)
            ->setRx($rx);
        
        $this->assertSame($this->monthlyTraffic, $result); // 测试链式调用返回值
        $this->assertSame($node, $this->monthlyTraffic->getNode());
        $this->assertSame($ip, $this->monthlyTraffic->getIp());
        $this->assertSame($month, $this->monthlyTraffic->getMonth());
        $this->assertSame($tx, $this->monthlyTraffic->getTx());
        $this->assertSame($rx, $this->monthlyTraffic->getRx());
    }
    
    public function testMonthValidation(): void
    {
        // 测试月份边界值
        $validMonths = [
            '2020-01', // 年份开始
            '2025-12', // 年份结束
            '2024-02', // 闰年二月
        ];
        
        foreach ($validMonths as $month) {
            $this->monthlyTraffic->setMonth($month);
            $this->assertSame($month, $this->monthlyTraffic->getMonth());
        }
    }
} 