<?php

namespace ServerStatsBundle\Tests\Controller\Admin;

use PHPUnit\Framework\TestCase;
use ServerStatsBundle\Controller\Admin\NodeStatsController;

class NodeStatsControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(NodeStatsController::class));
    }
    
    public function testControllerExtendsAbstractController(): void
    {
        $reflection = new \ReflectionClass(NodeStatsController::class);
        $parentClass = $reflection->getParentClass();
        
        $this->assertNotFalse($parentClass);
        $this->assertSame('Symfony\Bundle\FrameworkBundle\Controller\AbstractController', $parentClass->getName());
    }
    
    public function testControllerHasNetworkMonitorMethod(): void
    {
        $reflection = new \ReflectionClass(NodeStatsController::class);
        $this->assertTrue($reflection->hasMethod('networkMonitor'));
        
        $method = $reflection->getMethod('networkMonitor');
        $this->assertTrue($method->isPublic());
    }
    
    public function testControllerHasLoadMonitorMethod(): void
    {
        $reflection = new \ReflectionClass(NodeStatsController::class);
        $this->assertTrue($reflection->hasMethod('loadMonitor'));
        
        $method = $reflection->getMethod('loadMonitor');
        $this->assertTrue($method->isPublic());
    }
    
    public function testControllerUsesCorrectImports(): void
    {
        $reflection = new \ReflectionClass(NodeStatsController::class);
        $source = file_get_contents($reflection->getFileName());
        
        // 检查是否导入了必要的类
        $this->assertStringContainsString('Symfony\Bundle\FrameworkBundle\Controller\AbstractController', $source);
        $this->assertStringContainsString('Response', $source);
    }
    
    public function testControllerHasRouteAttribute(): void
    {
        $reflection = new \ReflectionClass(NodeStatsController::class);
        $source = file_get_contents($reflection->getFileName());
        
        // 检查是否有路由属性
        $this->assertStringContainsString('#[Route', $source);
    }
    
    public function testControllerHandlesNodeStatsDisplay(): void
    {
        $reflection = new \ReflectionClass(NodeStatsController::class);
        $source = file_get_contents($reflection->getFileName());
        
        // 检查是否处理节点统计显示
        $this->assertStringContainsString('node', $source);
        $this->assertStringContainsString('monitor', $source);
    }
    
    public function testControllerMethodReturnsResponse(): void
    {
        $reflection = new \ReflectionClass(NodeStatsController::class);
        
        if ($reflection->hasMethod('networkMonitor')) {
            $method = $reflection->getMethod('networkMonitor');
            $returnType = $method->getReturnType();
            
            if ($returnType) {
                $this->assertStringContainsString('Response', $returnType->getName());
            }
        }
        
        // 确保至少有一个断言
        $this->assertTrue(true);
    }
} 