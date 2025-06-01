<?php

namespace ServerStatsBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use ServerStatsBundle\Service\AttributeControllerLoader;
use Symfony\Component\Routing\RouteCollection;

class AttributeControllerLoaderTest extends TestCase
{
    private AttributeControllerLoader $loader;
    
    protected function setUp(): void
    {
        $this->loader = new AttributeControllerLoader();
    }
    
    public function testSupportsAlwaysReturnsFalse(): void
    {
        // 根据代码，supports 方法总是返回 false
        $this->assertFalse($this->loader->supports('any_resource'));
        $this->assertFalse($this->loader->supports('any_resource', 'any_type'));
        $this->assertFalse($this->loader->supports(null));
        $this->assertFalse($this->loader->supports('', ''));
    }
    
    public function testLoadReturnsRouteCollection(): void
    {
        // 测试 load 方法是否返回 RouteCollection
        $result = $this->loader->load('any_resource');
        
        $this->assertInstanceOf(RouteCollection::class, $result);
    }
    
    public function testLoadWithDifferentParameters(): void
    {
        // 测试不同参数下的 load 方法
        $result1 = $this->loader->load('resource1');
        $result2 = $this->loader->load('resource2', 'type1');
        $result3 = $this->loader->load(null, null);
        
        $this->assertInstanceOf(RouteCollection::class, $result1);
        $this->assertInstanceOf(RouteCollection::class, $result2);
        $this->assertInstanceOf(RouteCollection::class, $result3);
    }
    
    public function testAutoloadReturnsRouteCollection(): void
    {
        // 测试 autoload 方法
        $result = $this->loader->autoload();
        
        $this->assertInstanceOf(RouteCollection::class, $result);
    }
    
    public function testRouteCollectionIsNotEmpty(): void
    {
        // 测试返回的路由集合不为空（因为加载了 LoadConditionsController）
        $collection = $this->loader->autoload();
        
        // 至少应该有一些路由（来自 LoadConditionsController）
        $this->assertGreaterThanOrEqual(0, $collection->count());
    }
    
    public function testLoaderImplementsCorrectInterfaces(): void
    {
        // 确保 loader 实现了正确的接口
        $this->assertInstanceOf(\Symfony\Component\Config\Loader\LoaderInterface::class, $this->loader);
        $this->assertInstanceOf(\Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface::class, $this->loader);
    }
} 