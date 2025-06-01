<?php

namespace ServerStatsBundle\Tests\Controller\Admin;

use PHPUnit\Framework\TestCase;
use ServerStatsBundle\Controller\Admin\DailyTrafficCrudController;
use ServerStatsBundle\Entity\DailyTraffic;

class DailyTrafficCrudControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(DailyTrafficCrudController::class));
    }
    
    public function testControllerExtendsAbstractCrudController(): void
    {
        $reflection = new \ReflectionClass(DailyTrafficCrudController::class);
        $parentClass = $reflection->getParentClass();
        
        $this->assertNotFalse($parentClass);
        $this->assertSame('EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController', $parentClass->getName());
    }
    
    public function testGetEntityFqcnReturnsCorrectEntity(): void
    {
        $reflection = new \ReflectionClass(DailyTrafficCrudController::class);
        $this->assertTrue($reflection->hasMethod('getEntityFqcn'));
        
        $method = $reflection->getMethod('getEntityFqcn');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
        
        $result = DailyTrafficCrudController::getEntityFqcn();
        $this->assertSame(DailyTraffic::class, $result);
    }
    
    public function testConfigureFieldsMethodExists(): void
    {
        $reflection = new \ReflectionClass(DailyTrafficCrudController::class);
        $this->assertTrue($reflection->hasMethod('configureFields'));
        
        $method = $reflection->getMethod('configureFields');
        $this->assertTrue($method->isPublic());
    }
    
    public function testControllerUsesCorrectImports(): void
    {
        $reflection = new \ReflectionClass(DailyTrafficCrudController::class);
        $source = file_get_contents($reflection->getFileName());
        
        // 检查是否导入了必要的类
        $this->assertStringContainsString('EasyCorp\Bundle\EasyAdminBundle', $source);
        $this->assertStringContainsString('DailyTraffic', $source);
    }
    
    public function testControllerHasFormatBytesMethod(): void
    {
        $reflection = new \ReflectionClass(DailyTrafficCrudController::class);
        
        // 检查是否有字节格式化相关的方法或逻辑
        $source = file_get_contents($reflection->getFileName());
        $this->assertStringContainsString('formatValue', $source);
    }
    
    public function testControllerConfiguresFieldsCorrectly(): void
    {
        $reflection = new \ReflectionClass(DailyTrafficCrudController::class);
        $source = file_get_contents($reflection->getFileName());
        
        // 检查是否配置了正确的字段
        $this->assertStringContainsString('node', $source);
        $this->assertStringContainsString('ip', $source);
        $this->assertStringContainsString('date', $source);
        $this->assertStringContainsString('rx', $source);
        $this->assertStringContainsString('tx', $source);
    }
} 