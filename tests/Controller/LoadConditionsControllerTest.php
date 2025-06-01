<?php

namespace ServerStatsBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use ServerStatsBundle\Controller\LoadConditionsController;

class LoadConditionsControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(LoadConditionsController::class));
    }
    
    public function testControllerHasCorrectMethods(): void
    {
        $this->assertTrue(method_exists(LoadConditionsController::class, 'loadConditions'));
    }
    
    public function testLoadConditionsMethodExists(): void
    {
        // 测试 loadConditions 方法存在
        $reflection = new \ReflectionClass(LoadConditionsController::class);
        $this->assertTrue($reflection->hasMethod('loadConditions'));
        
        $method = $reflection->getMethod('loadConditions');
        $this->assertTrue($method->isPublic());
    }
    
    public function testControllerUsesCorrectEntities(): void
    {
        // 测试控制器使用了正确的实体类
        $reflection = new \ReflectionClass(LoadConditionsController::class);
        $source = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('DailyTraffic', $source);
        $this->assertStringContainsString('MonthlyTraffic', $source);
        $this->assertStringContainsString('MinuteStat', $source);
        $this->assertStringContainsString('Node', $source);
    }
    
    public function testControllerHasRouteAttributes(): void
    {
        // 测试控制器有路由属性
        $reflection = new \ReflectionClass(LoadConditionsController::class);
        $source = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('#[Route', $source);
    }
    
    public function testControllerMethodsReturnCorrectTypes(): void
    {
        // 测试方法返回类型
        $reflection = new \ReflectionClass(LoadConditionsController::class);
        
        if ($reflection->hasMethod('loadConditions')) {
            $loadConditionsMethod = $reflection->getMethod('loadConditions');
            $returnType = $loadConditionsMethod->getReturnType();
            if ($returnType) {
                $this->assertStringContainsString('Response', $returnType->getName());
            }
        }
    }
    
    public function testControllerHandlesRequestCorrectly(): void
    {
        // 测试控制器处理请求的基本结构
        $reflection = new \ReflectionClass(LoadConditionsController::class);
        $source = file_get_contents($reflection->getFileName());
        
        // 检查是否使用了 Request 对象
        $this->assertStringContainsString('Request', $source);
        
        // 检查是否有 JSON 响应处理
        $this->assertStringContainsString('json', $source);
    }
    
    public function testControllerHasCorrectDependencies(): void
    {
        // 测试控制器有正确的依赖注入
        $reflection = new \ReflectionClass(LoadConditionsController::class);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor);
        $this->assertGreaterThan(0, $constructor->getNumberOfParameters());
        
        $parameters = $constructor->getParameters();
        $parameterTypes = array_map(fn($param) => $param->getType()?->getName(), $parameters);
        
        // 检查是否包含必要的依赖
        $this->assertContains('Symfony\Component\Cache\Adapter\AdapterInterface', $parameterTypes);
        $this->assertContains('ServerNodeBundle\Repository\NodeRepository', $parameterTypes);
        $this->assertContains('Doctrine\ORM\EntityManagerInterface', $parameterTypes);
    }
    
    public function testControllerExtendsAbstractController(): void
    {
        // 测试控制器继承自 AbstractController
        $reflection = new \ReflectionClass(LoadConditionsController::class);
        $parentClass = $reflection->getParentClass();
        
        $this->assertNotFalse($parentClass);
        $this->assertSame('Symfony\Bundle\FrameworkBundle\Controller\AbstractController', $parentClass->getName());
    }
} 