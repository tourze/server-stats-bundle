<?php

namespace ServerStatsBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use ServerStatsBundle\DependencyInjection\ServerStatsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(ServerStatsExtension::class)]
final class ServerStatsExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private ServerStatsExtension $extension;

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new ServerStatsExtension();
        $this->container = new ContainerBuilder();
        // 设置 kernel.environment 参数以避免测试错误
        $this->container->setParameter('kernel.environment', 'test');
    }

    public function testExtensionImplementsInterface(): void
    {
        $this->assertInstanceOf(ExtensionInterface::class, $this->extension);
    }

    public function testLoadBasicConfiguration(): void
    {
        $configs = [];
        $this->extension->load($configs, $this->container);

        $this->assertInstanceOf(ContainerBuilder::class, $this->container);
    }
}
