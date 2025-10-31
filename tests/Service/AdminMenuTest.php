<?php

declare(strict_types=1);

namespace ServerStatsBundle\Tests\Service;

use Knp\Menu\MenuFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ServerStatsBundle\Service\AdminMenu;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * AdminMenu服务测试
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // Setup for AdminMenu tests
    }

    public function testInvokeAddsMenuItems(): void
    {
        $container = self::getContainer();
        $adminMenu = $container->get(AdminMenu::class);
        self::assertInstanceOf(AdminMenu::class, $adminMenu);

        $factory = new MenuFactory();
        $rootItem = $factory->createItem('root');

        $adminMenu->__invoke($rootItem);

        // 验证菜单结构
        $serverMenu = $rootItem->getChild('服务器管理');
        self::assertNotNull($serverMenu);

        $statsMenu = $serverMenu->getChild('服务器统计');
        self::assertNotNull($statsMenu);

        self::assertNotNull($statsMenu->getChild('分钟统计'));
        self::assertNotNull($statsMenu->getChild('日流量统计'));
        self::assertNotNull($statsMenu->getChild('月流量统计'));
    }
}
