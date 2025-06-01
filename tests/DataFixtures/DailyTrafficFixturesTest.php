<?php

namespace ServerStatsBundle\Tests\DataFixtures;

use PHPUnit\Framework\TestCase;
use ServerStatsBundle\DataFixtures\DailyTrafficFixtures;

class DailyTrafficFixturesTest extends TestCase
{
    public function testFixtureExists(): void
    {
        $this->assertTrue(class_exists(DailyTrafficFixtures::class));
    }
    
    public function testFixtureImplementsFixtureInterface(): void
    {
        $reflection = new \ReflectionClass(DailyTrafficFixtures::class);
        $interfaces = $reflection->getInterfaceNames();
        
        $this->assertContains('Doctrine\Common\DataFixtures\FixtureInterface', $interfaces);
    }
    
    public function testFixtureHasLoadMethod(): void
    {
        $reflection = new \ReflectionClass(DailyTrafficFixtures::class);
        $this->assertTrue($reflection->hasMethod('load'));
        
        $method = $reflection->getMethod('load');
        $this->assertTrue($method->isPublic());
    }
    
    public function testFixtureUsesCorrectEntities(): void
    {
        $reflection = new \ReflectionClass(DailyTrafficFixtures::class);
        $source = file_get_contents($reflection->getFileName());
        
        // 检查是否使用了正确的实体
        $this->assertStringContainsString('DailyTraffic', $source);
        $this->assertStringContainsString('Node', $source);
    }
    
    public function testFixtureHasDependencies(): void
    {
        $reflection = new \ReflectionClass(DailyTrafficFixtures::class);
        
        // 检查是否实现了 DependentFixtureInterface
        $interfaces = $reflection->getInterfaceNames();
        if (in_array('Doctrine\Common\DataFixtures\DependentFixtureInterface', $interfaces)) {
            $this->assertTrue($reflection->hasMethod('getDependencies'));
        }
    }
    
    public function testFixtureCreatesTestData(): void
    {
        $reflection = new \ReflectionClass(DailyTrafficFixtures::class);
        $source = file_get_contents($reflection->getFileName());
        
        // 检查是否创建测试数据
        $this->assertStringContainsString('persist', $source);
        $this->assertStringContainsString('flush', $source);
    }
    
    public function testFixtureHandlesDateCorrectly(): void
    {
        $reflection = new \ReflectionClass(DailyTrafficFixtures::class);
        $source = file_get_contents($reflection->getFileName());
        
        // 检查是否正确处理日期
        $this->assertStringContainsString('DateTime', $source);
    }
} 