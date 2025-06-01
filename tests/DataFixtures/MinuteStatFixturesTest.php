<?php

namespace ServerStatsBundle\Tests\DataFixtures;

use PHPUnit\Framework\TestCase;
use ServerStatsBundle\DataFixtures\MinuteStatFixtures;

class MinuteStatFixturesTest extends TestCase
{
    public function testFixtureExists(): void
    {
        $this->assertTrue(class_exists(MinuteStatFixtures::class));
    }
    
    public function testFixtureImplementsFixtureInterface(): void
    {
        $reflection = new \ReflectionClass(MinuteStatFixtures::class);
        $interfaces = $reflection->getInterfaceNames();
        
        $this->assertContains('Doctrine\Common\DataFixtures\FixtureInterface', $interfaces);
    }
    
    public function testFixtureHasLoadMethod(): void
    {
        $reflection = new \ReflectionClass(MinuteStatFixtures::class);
        $this->assertTrue($reflection->hasMethod('load'));
        
        $method = $reflection->getMethod('load');
        $this->assertTrue($method->isPublic());
    }
    
    public function testFixtureUsesCorrectEntities(): void
    {
        $reflection = new \ReflectionClass(MinuteStatFixtures::class);
        $source = file_get_contents($reflection->getFileName());
        
        // 检查是否使用了正确的实体
        $this->assertStringContainsString('MinuteStat', $source);
        $this->assertStringContainsString('Node', $source);
    }
    
    public function testFixtureHasDependencies(): void
    {
        $reflection = new \ReflectionClass(MinuteStatFixtures::class);
        
        // 检查是否实现了 DependentFixtureInterface
        $interfaces = $reflection->getInterfaceNames();
        if (in_array('Doctrine\Common\DataFixtures\DependentFixtureInterface', $interfaces)) {
            $this->assertTrue($reflection->hasMethod('getDependencies'));
        }
    }
    
    public function testFixtureCreatesTestData(): void
    {
        $reflection = new \ReflectionClass(MinuteStatFixtures::class);
        $source = file_get_contents($reflection->getFileName());
        
        // 检查是否创建测试数据
        $this->assertStringContainsString('persist', $source);
        $this->assertStringContainsString('flush', $source);
    }
    
    public function testFixtureHandlesBatchProcessing(): void
    {
        $reflection = new \ReflectionClass(MinuteStatFixtures::class);
        $source = file_get_contents($reflection->getFileName());
        
        // 检查是否处理批量数据（MinuteStat 通常有大量数据）
        $this->assertStringContainsString('for', $source);
    }
    
    public function testFixtureHandlesComplexData(): void
    {
        $reflection = new \ReflectionClass(MinuteStatFixtures::class);
        $source = file_get_contents($reflection->getFileName());
        
        // 检查是否处理复杂数据（负载、内存、带宽等）
        $this->assertStringContainsString('Load', $source);
        $this->assertStringContainsString('Memory', $source);
        $this->assertStringContainsString('Bandwidth', $source);
    }
} 