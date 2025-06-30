<?php

namespace ServerStatsBundle\Tests\Controller\Admin;

use PHPUnit\Framework\TestCase;
use ServerStatsBundle\Controller\Admin\NodeLoadMonitorController;

class NodeLoadMonitorControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(NodeLoadMonitorController::class));
    }
    
    public function testExtendsAbstractController(): void
    {
        $reflection = new \ReflectionClass(NodeLoadMonitorController::class);
        $parentClass = $reflection->getParentClass();
        
        $this->assertNotFalse($parentClass);
        $this->assertSame('Symfony\Bundle\FrameworkBundle\Controller\AbstractController', $parentClass->getName());
    }
    
    public function testHasConstructor(): void
    {
        $reflection = new \ReflectionClass(NodeLoadMonitorController::class);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor);
        $this->assertSame(1, $constructor->getNumberOfParameters());
        
        $parameter = $constructor->getParameters()[0];
        $this->assertSame('nodeMonitorService', $parameter->getName());
        
        $type = $parameter->getType();
        if ($type instanceof \ReflectionNamedType) {
            $this->assertSame('ServerStatsBundle\Service\NodeMonitorService', $type->getName());
        }
    }
    
    public function testHasInvokeMethod(): void
    {
        $reflection = new \ReflectionClass(NodeLoadMonitorController::class);
        $this->assertTrue($reflection->hasMethod('__invoke'));
        
        $method = $reflection->getMethod('__invoke');
        $this->assertTrue($method->isPublic());
        $this->assertSame(2, $method->getNumberOfParameters());
        
        $returnType = $method->getReturnType();
        if ($returnType instanceof \ReflectionNamedType) {
            $this->assertSame('Symfony\Component\HttpFoundation\Response', $returnType->getName());
        }
    }
    
    public function testHasRouteAttribute(): void
    {
        $reflection = new \ReflectionClass(NodeLoadMonitorController::class);
        $method = $reflection->getMethod('__invoke');
        $attributes = $method->getAttributes('Symfony\Component\Routing\Attribute\Route');
        
        $this->assertCount(1, $attributes);
        
        $routeAttribute = $attributes[0];
        $arguments = $routeAttribute->getArguments();
        
        $this->assertArrayHasKey('path', $arguments);
        $this->assertSame('/admin/node-stats/{id}/load-monitor', $arguments['path']);
        $this->assertArrayHasKey('name', $arguments);
        $this->assertSame('server_stats_node_load_monitor', $arguments['name']);
    }
    
    public function testUsesCorrectServices(): void
    {
        $reflection = new \ReflectionClass(NodeLoadMonitorController::class);
        $source = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('NodeMonitorService', $source);
        $this->assertStringContainsString('getLoadMonitorData', $source);
        $this->assertStringContainsString('@ServerStats/admin/load_monitor.html.twig', $source);
    }
    
    public function testParameterTypes(): void
    {
        $reflection = new \ReflectionClass(NodeLoadMonitorController::class);
        $method = $reflection->getMethod('__invoke');
        $parameters = $method->getParameters();
        
        $this->assertCount(2, $parameters);
        
        $nodeParam = $parameters[0];
        $this->assertSame('node', $nodeParam->getName());
        $type = $nodeParam->getType();
        if ($type instanceof \ReflectionNamedType) {
            $this->assertSame('ServerNodeBundle\Entity\Node', $type->getName());
        }
        
        $requestParam = $parameters[1];
        $this->assertSame('request', $requestParam->getName());
        $type = $requestParam->getType();
        if ($type instanceof \ReflectionNamedType) {
            $this->assertSame('Symfony\Component\HttpFoundation\Request', $type->getName());
        }
    }
}