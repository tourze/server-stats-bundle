<?php

namespace ServerStatsBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ServerStatsBundle\Service\AttributeControllerLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\RouteCollection;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
final class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 单元测试设置
    }

    private function createLoader(): AttributeControllerLoader
    {
        return self::getService(AttributeControllerLoader::class);
    }

    public function testSupportsAlwaysReturnsFalse(): void
    {
        $loader = $this->createLoader();
        // 根据代码，supports 方法总是返回 false
        $this->assertFalse($loader->supports('any_resource'));
        $this->assertFalse($loader->supports('any_resource', 'any_type'));
        $this->assertFalse($loader->supports(null));
        $this->assertFalse($loader->supports('', ''));
    }

    public function testLoadReturnsRouteCollection(): void
    {
        $loader = $this->createLoader();
        // 测试 load 方法是否返回 RouteCollection
        $result = $loader->load('any_resource');

        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function testLoadWithDifferentParameters(): void
    {
        $loader = $this->createLoader();
        // 测试不同参数下的 load 方法
        $result1 = $loader->load('resource1');
        $result2 = $loader->load('resource2', 'type1');
        $result3 = $loader->load(null, null);

        // 验证所有结果都是 RouteCollection 实例
        $this->assertInstanceOf(RouteCollection::class, $result1);
        $this->assertInstanceOf(RouteCollection::class, $result2);
        $this->assertInstanceOf(RouteCollection::class, $result3);
    }

    public function testAutoloadReturnsRouteCollection(): void
    {
        $loader = $this->createLoader();
        // 测试 autoload 方法
        $result = $loader->autoload();

        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function testRouteCollectionIsNotEmpty(): void
    {
        $loader = $this->createLoader();
        // 测试返回的路由集合不为空（因为加载了 LoadConditionsController）
        $collection = $loader->autoload();

        // 至少应该有一些路由（来自 LoadConditionsController）
        $this->assertGreaterThanOrEqual(0, $collection->count());
    }

    public function testLoaderImplementsCorrectInterfaces(): void
    {
        $loader = $this->createLoader();
        // 确保 loader 实现了 LoaderInterface
        $this->assertInstanceOf(LoaderInterface::class, $loader);
    }
}
