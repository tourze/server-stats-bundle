<?php

declare(strict_types=1);

namespace ServerStatsBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use ServerStatsBundle\Entity\MonthlyTraffic;

/**
 * @extends AbstractCrudController<MonthlyTraffic>
 */
#[AdminCrud(routePath: '/server-stats/monthly-traffic', routeName: 'server_stats_monthly_traffic')]
final class MonthlyTrafficCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MonthlyTraffic::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('月流量')
            ->setEntityLabelInPlural('月流量统计')
            ->setPageTitle('index', '服务器月流量统计')
            ->setHelp('index', '查看服务器节点的每月流量统计数据，包括上行流量和下行流量')
            ->setDefaultSort(['month' => 'DESC'])
            ->setSearchFields(['ip', 'node.name', 'month'])
            ->setPaginatorPageSize(30)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield FormField::addPanel('基本信息')
            ->setIcon('fa fa-info-circle')
        ;

        yield AssociationField::new('node', '节点');

        yield TextField::new('ip', 'IP地址');

        yield TextField::new('month', '月份')
            ->setHelp('格式：YYYY-MM，例如：2024-01')
            ->setFormTypeOptions([
                'attr' => [
                    'pattern' => '\d{4}-\d{2}',
                    'placeholder' => '2024-01',
                ],
            ])
        ;

        yield FormField::addPanel('流量数据')
            ->setIcon('fa fa-exchange-alt')
        ;

        yield TextField::new('rx', '下行流量')
            ->formatValue(function ($value) {
                if (!$value) {
                    return null;
                }

                return $this->formatBytes((int) $value);
            })
        ;

        yield TextField::new('tx', '上行流量')
            ->formatValue(function ($value) {
                if (!$value) {
                    return null;
                }

                return $this->formatBytes((int) $value);
            })
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('node', '节点'))
            ->add(TextFilter::new('ip', 'IP地址'))
            ->add(TextFilter::new('month', '月份'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
        ;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes > 0 ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * (int) $pow));

        return round($bytes, $precision) . ' ' . $units[(int) $pow];
    }
}
