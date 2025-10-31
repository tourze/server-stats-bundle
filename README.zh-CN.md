# Server Stats Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/server-stats-bundle.svg?style=flat-square)]
(https://packagist.org/packages/tourze/server-stats-bundle)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg?style=flat-square)](https://php.net)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?style=flat-square)]
(https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo?style=flat-square)]
(https://codecov.io/gh/tourze/php-monorepo)

一个功能强大的 Symfony Bundle，用于收集、存储和可视化全面的服务器统计信息和监控数据。

## 目录

- [特性](#特性)
- [安装](#安装)
- [系统要求](#系统要求)
- [快速开始](#快速开始)
- [配置](#配置)
- [API 端点](#api-端点)
- [使用示例](#使用示例)
- [高级用法](#高级用法)
- [许可证](#许可证)

## 特性

- **全面的服务器监控**: CPU 使用率、内存消耗、系统负载和进程统计
- **网络流量分析**: 实时带宽监控和 RX/TX 统计
- **存储和磁盘 I/O**: 全面的磁盘性能指标，包括 IOPS 和等待时间
- **TCP/UDP 连接跟踪**: 监控网络连接和协议统计
- **多时间框图表**: 24小时和7天可视化，带有自动数据聚合
- **EasyAdmin 集成**: 内置的管理界面用于数据管理和可视化
- **自动数据清理**: 可配置的保留策略和定时清理
- **历史数据聚合**: 日度和月度流量汇总，用于长期分析

## 安装

```bash
composer require tourze/server-stats-bundle
```

## 系统要求

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- Doctrine ORM 3.0 或更高版本
- EasyAdmin Bundle 4.0 或更高版本

## 快速开始

### 1. 启用 Bundle

将 bundle 添加到您的 Symfony 应用程序中：

```php
// config/bundles.php
return [
    // ... 其他 bundles
    ServerStatsBundle\ServerStatsBundle::class => ['all' => true],
];
```

### 2. 创建数据库表

运行以下命令创建所需的数据库表：

```bash
php bin/console doctrine:migrations:migrate
```

### 3. 配置数据收集

Bundle 提供了几个用于存储服务器统计信息的实体：

- **`MinuteStat`** - 详细的分钟级服务器统计，包括：
  - CPU 指标（用户、系统、被盗、空闲百分比）
  - 内存统计（总量、已用、空闲、可用、缓冲区、缓存）
  - 系统负载平均值（1分钟、5分钟、15分钟）
  - 进程计数（运行中、总数、等待中）
  - 网络带宽（RX/TX）
  - 磁盘 I/O 性能指标
  - TCP/UDP 连接统计
  - 在线用户跟踪

- **`DailyTraffic`** - 按 IP 和节点的每日流量聚合
- **`MonthlyTraffic`** - 用于长期分析的每月流量汇总

### 4. 访问仪表板

配置完成后，您可以通过 EasyAdmin 界面访问监控仪表板：

- **负载监控**: `/admin/load-monitor/{nodeId}` - CPU、内存和进程监控
- **网络监控**: `/admin/network-monitor/{nodeId}` - 带有图表的网络流量分析

## API 端点

### 负载状况

获取服务器负载状况：

```text
GET /api/load-conditions/{nodeId}
```

响应：
```json
{
  "avgCpuUsage": 45.2,
  "avgLoad": 2.1,
  "avgMemUsage": 68.5,
  "avgProcess": 120
}
```

## 配置

Bundle 通过依赖注入自动配置。无需额外配置。

### 数据保留

Bundle 包含对 `MinuteStat` 实体的自动数据清理：

- **默认保留时间**: 60 天
- **清理计划**: 每日凌晨 5:14
- **环境变量**: `SERVER_NODE_STAT_PERSIST_DAY_NUM` 用于自定义保留时间

### 监控仪表板

Bundle 提供两个主要的仪表板视图：

1. **负载监控** (`/admin/load-monitor/{nodeId}`):
    - CPU 使用率图表（用户、系统、被盗、空闲）
    - 内存利用率图表
    - 系统负载平均值
    - 进程数量监控
    - 平均值汇总卡片

2. **网络监控** (`/admin/network-monitor/{nodeId}`):
    - 24小时带宽图表
    - 7天流量趋势
    - RX/TX 数据可视化
    - 实时网络统计

## 使用示例

### 使用 NodeMonitorService

```php
use ServerStatsBundle\Service\NodeMonitorService;
use ServerNodeBundle\Entity\Node;

class YourController
{
    public function __construct(
        private NodeMonitorService $nodeMonitorService
    ) {}

    public function getNetworkStats(Node $node): array
    {
        // 返回 24 小时和 7 天的网络监控数据
        $data = $this->nodeMonitorService->getNetworkMonitorData($node);
        
        // 可用数据包括：
        // - labels24h: 24小时图表的小时标签
        // - rxData24h/txData24h: 24小时带宽数据
        // - labels7d: 7天图表的日期标签 
        // - rxData7d/txData7d: 7天带宽数据
        return $data;
    }

    public function getLoadStats(Node $node): array
    {
        // 返回全面的负载监控数据
        $data = $this->nodeMonitorService->getLoadMonitorData($node);
        
        // 可用数据包括：
        // - CPU 指标: cpuUserData, cpuSystemData, cpuIdleData
        // - 负载平均值: loadOneData, loadFiveData, loadFifteenData
        // - 内存数据: memoryTotalData, memoryUsedData, memoryFreeData
        // - 进程数据: processRunningData, processTotalData
        // - 汇总平均值: avgCpuUsage, avgLoad, avgMemUsage, avgProcess
        return $data;
    }
}
```

### 使用 Repository

```php
use ServerStatsBundle\Repository\MinuteStatRepository;
use ServerStatsBundle\Repository\DailyTrafficRepository;
use ServerStatsBundle\Repository\MonthlyTrafficRepository;
use ServerStatsBundle\Entity\DailyTraffic;
use ServerStatsBundle\Entity\MonthlyTraffic;
use ServerNodeBundle\Entity\Node;

class StatsService
{
    public function __construct(
        private MinuteStatRepository $minuteStatRepository,
        private DailyTrafficRepository $dailyTrafficRepository,
        private MonthlyTrafficRepository $monthlyTrafficRepository
    ) {}

    public function getRecentStats(Node $node): array
    {
        // 获取最近的分钟级统计数据
        $qb = $this->minuteStatRepository->createQueryBuilder('s')
            ->where('s.node = :node')
            ->andWhere('s.datetime >= :startTime')
            ->setParameter('node', $node)
            ->setParameter('startTime', new \DateTime('-1 hour'))
            ->orderBy('s.datetime', 'DESC')
            ->setMaxResults(60);
            
        return $qb->getQuery()->getResult();
    }
    
    public function getDailyTraffic(Node $node, \DateTimeInterface $date): ?DailyTraffic
    {
        return $this->dailyTrafficRepository->findOneBy([
            'node' => $node,
            'date' => $date
        ]);
    }
    
    public function getMonthlyTraffic(Node $node, \DateTimeInterface $month): ?MonthlyTraffic
    {
        return $this->monthlyTrafficRepository->findOneBy([
            'node' => $node,
            'month' => $month
        ]);
    }
}
```

## 高级用法

### 自定义数据收集

您可以通过创建自定义收集器来扩展包的数据收集功能：

```php
use ServerStatsBundle\Entity\MinuteStat;
use ServerNodeBundle\Entity\Node;
use Doctrine\ORM\EntityManagerInterface;

class CustomStatsCollector
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function collectCustomStats(Node $node): void
    {
        $stat = new MinuteStat();
        $stat->setNode($node);
        $stat->setDatetime(new \DateTime());
        
        // 设置自定义指标
        $stat->setCpuUserPercent($this->getCpuUsage());
        $stat->setMemoryUsed($this->getMemoryUsage());
        // ... 其他指标
        
        $this->entityManager->persist($stat);
        $this->entityManager->flush();
    }

    private function getCpuUsage(): float
    {
        // 您的自定义 CPU 收集逻辑
        return 0.0;
    }

    private function getMemoryUsage(): int
    {
        // 您的自定义内存收集逻辑
        return 0;
    }
}
```

### 数据聚合

为了性能优化，您可以创建自定义数据聚合器：

```php
use ServerStatsBundle\Repository\MinuteStatRepository;
use ServerStatsBundle\Entity\DailyTraffic;

class DataAggregator
{
    public function __construct(
        private MinuteStatRepository $minuteStatRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function aggregateDailyData(Node $node, \DateTimeInterface $date): void
    {
        $startDate = (clone $date)->setTime(0, 0, 0);
        $endDate = (clone $date)->setTime(23, 59, 59);
        
        $qb = $this->minuteStatRepository->createQueryBuilder('s')
            ->select('SUM(s.rxBandwidth) as totalRx, SUM(s.txBandwidth) as totalTx')
            ->where('s.node = :node')
            ->andWhere('s.datetime BETWEEN :start AND :end')
            ->setParameter('node', $node)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);
            
        $result = $qb->getQuery()->getSingleResult();
        
        // 创建日聚合
        $dailyTraffic = new DailyTraffic();
        $dailyTraffic->setNode($node);
        $dailyTraffic->setDate($date);
        $dailyTraffic->setRx($result['totalRx']);
        $dailyTraffic->setTx($result['totalTx']);
        
        $this->entityManager->persist($dailyTraffic);
        $this->entityManager->flush();
    }
}
```

### 自定义监控仪表板

为专门的监控视图创建自定义仪表板控制器：

```php
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use ServerStatsBundle\Service\NodeMonitorService;
use ServerNodeBundle\Entity\Node;

#[Route('/admin/custom-monitor')]
class CustomMonitorController extends AbstractController
{
    #[Route('/{id}/performance', name: 'admin_custom_performance_monitor')]
    public function performanceMonitor(
        Node $node,
        NodeMonitorService $nodeMonitorService
    ): Response {
        $data = $nodeMonitorService->getLoadMonitorData($node);
        
        // 添加自定义计算
        $data['performanceScore'] = $this->calculatePerformanceScore($data);
        
        return $this->render('admin/custom_performance_monitor.html.twig', [
            'node' => $node,
            'data' => $data,
        ]);
    }
    
    private function calculatePerformanceScore(array $data): float
    {
        // 自定义性能评分逻辑
        $cpuScore = 100 - $data['avgCpuUsage'];
        $memScore = 100 - $data['avgMemUsage'];
        $loadScore = max(0, 100 - ($data['avgLoad'] * 50));
        
        return ($cpuScore + $memScore + $loadScore) / 3;
    }
}
```

## 贡献

欢迎贡献！请遵循以下指南：

1. **Fork 仓库**并从 `master` 创建您的功能分支
2. **为您的更改编写测试**，使用 PHPUnit
3. **遵循代码标准** - 使用 PHPStan 和编码风格指南
4. **更新文档**，如果您添加了新功能
5. **提交拉取请求**，清楚地描述您的更改

### 运行测试

```bash
# 运行所有测试
./vendor/bin/phpunit packages/server-stats-bundle/tests

# 运行 PHPStan 分析
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/server-stats-bundle

# 运行包检查
bin/console app:check-packages server-stats-bundle -o -f
```

### 代码质量

请确保您的代码通过所有质量检查：
- 所有测试必须通过
- PHPStan 分析必须是干净的（级别 9）
- 遵循 PSR-12 编码标准
- 添加适当的类型声明

## 许可证

此 Bundle 在 MIT 许可证下发布。详情请查看随附的 LICENSE 文件。
