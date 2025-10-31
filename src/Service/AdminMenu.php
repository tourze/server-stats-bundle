<?php

declare(strict_types=1);

namespace ServerStatsBundle\Service;

use Knp\Menu\ItemInterface;
use ServerStatsBundle\Entity\DailyTraffic;
use ServerStatsBundle\Entity\MinuteStat;
use ServerStatsBundle\Entity\MonthlyTraffic;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

/**
 * 服务器统计管理后台菜单提供者
 */
#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('服务器管理')) {
            $item->addChild('服务器管理')
                ->setAttribute('icon', 'fas fa-server')
            ;
        }

        $serverMenu = $item->getChild('服务器管理');
        if (null === $serverMenu) {
            return;
        }

        // 添加服务器统计子菜单
        if (null === $serverMenu->getChild('服务器统计')) {
            $serverMenu->addChild('服务器统计')
                ->setAttribute('icon', 'fas fa-chart-line')
            ;
        }

        $statsMenu = $serverMenu->getChild('服务器统计');
        if (null === $statsMenu) {
            return;
        }

        $statsMenu->addChild('分钟统计')
            ->setUri($this->linkGenerator->getCurdListPage(MinuteStat::class))
            ->setAttribute('icon', 'fas fa-clock')
        ;

        $statsMenu->addChild('日流量统计')
            ->setUri($this->linkGenerator->getCurdListPage(DailyTraffic::class))
            ->setAttribute('icon', 'fas fa-calendar-day')
        ;

        $statsMenu->addChild('月流量统计')
            ->setUri($this->linkGenerator->getCurdListPage(MonthlyTraffic::class))
            ->setAttribute('icon', 'fas fa-calendar-alt')
        ;
    }
}
