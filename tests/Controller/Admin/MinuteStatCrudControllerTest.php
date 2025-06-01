<?php

namespace ServerStatsBundle\Tests\Controller\Admin;

use PHPUnit\Framework\TestCase;
use ServerStatsBundle\Controller\Admin\MinuteStatCrudController;
use ServerStatsBundle\Entity\MinuteStat;

class MinuteStatCrudControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(MinuteStatCrudController::class));
    }
    
    public function testControllerExtendsAbstractCrudController(): void
    {
        $reflection = new \ReflectionClass(MinuteStatCrudController::class);
        $parentClass = $reflection->getParentClass();
        
        $this->assertNotFalse($parentClass);
        $this->assertSame('EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController', $parentClass->getName());
    }
    
    public function testGetEntityFqcnReturnsCorrectEntity(): void
    {
        $reflection = new \ReflectionClass(MinuteStatCrudController::class);
        $this->assertTrue($reflection->hasMethod('getEntityFqcn'));
        
        $method = $reflection->getMethod('getEntityFqcn');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
        
        $result = MinuteStatCrudController::getEntityFqcn();
        $this->assertSame(MinuteStat::class, $result);
    }
    
    public function testConfigureFieldsMethodExists(): void
    {
        $reflection = new \ReflectionClass(MinuteStatCrudController::class);
        $this->assertTrue($reflection->hasMethod('configureFields'));
        
        $method = $reflection->getMethod('configureFields');
        $this->assertTrue($method->isPublic());
    }
    
    public function testControllerUsesCorrectImports(): void
    {
        $reflection = new \ReflectionClass(MinuteStatCrudController::class);
        $source = file_get_contents($reflection->getFileName());
        
        // 检查是否导入了必要的类
        $this->assertStringContainsString('EasyCorp\Bundle\EasyAdminBundle', $source);
        $this->assertStringContainsString('MinuteStat', $source);
    }
    
    public function testControllerHasFormatMethods(): void
    {
        $reflection = new \ReflectionClass(MinuteStatCrudController::class);
        $source = file_get_contents($reflection->getFileName());
        
        // 检查是否有格式化相关的方法或逻辑
        $this->assertStringContainsString('formatValue', $source);
    }
    
    public function testControllerConfiguresFieldsCorrectly(): void
    {
        $reflection = new \ReflectionClass(MinuteStatCrudController::class);
        $source = file_get_contents($reflection->getFileName());
        
        // 检查是否配置了正确的字段
        $this->assertStringContainsString('node', $source);
        $this->assertStringContainsString('datetime', $source);
        $this->assertStringContainsString('load', $source);
        $this->assertStringContainsString('memory', $source);
        $this->assertStringContainsString('Bandwidth', $source);
    }
    
    public function testControllerHandlesComplexFields(): void
    {
        $reflection = new \ReflectionClass(MinuteStatCrudController::class);
        $source = file_get_contents($reflection->getFileName());
        
        // 检查是否处理了复杂字段（如负载、内存、带宽）
        $this->assertStringContainsString('loadOneMinute', $source);
        $this->assertStringContainsString('memoryTotal', $source);
        $this->assertStringContainsString('Bandwidth', $source);
    }
} 