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

A powerful Symfony bundle for collecting, storing, and visualizing comprehensive server statistics and monitoring data.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Requirements](#requirements)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [API Endpoints](#api-endpoints)
- [Usage Examples](#usage-examples)
- [Advanced Usage](#advanced-usage)
- [License](#license)

## Features

- **Complete Server Monitoring**: CPU usage, memory consumption, system load, and process statistics
- **Network Traffic Analysis**: Real-time bandwidth monitoring with RX/TX statistics
- **Storage & Disk I/O**: Comprehensive disk performance metrics including IOPS and wait times
- **TCP/UDP Connection Tracking**: Monitor network connections and protocol statistics
- **Multi-timeframe Charts**: 24-hour and 7-day visualizations with automatic data aggregation
- **EasyAdmin Integration**: Built-in admin interface for data management and visualization
- **Automatic Data Cleanup**: Configurable retention policies with scheduled cleanup
- **Historical Data Aggregation**: Daily and monthly traffic summaries for long-term analysis

## Installation

```bash
composer require tourze/server-stats-bundle
```

## Requirements

- PHP 8.1 or higher
- Symfony 6.4 or higher
- Doctrine ORM 3.0 or higher
- EasyAdmin Bundle 4.0 or higher

## Quick Start

### 1. Enable the Bundle

Add the bundle to your Symfony application:

```php
// config/bundles.php
return [
    // ... other bundles
    ServerStatsBundle\ServerStatsBundle::class => ['all' => true],
];
```

### 2. Create Database Tables

Run the following commands to create the required database tables:

```bash
php bin/console doctrine:migrations:migrate
```

### 3. Configure Data Collection

The bundle provides several entities for storing server statistics:

- **`MinuteStat`** - Detailed minute-level server statistics including:
  - CPU metrics (user, system, stolen, idle percentages)
  - Memory statistics (total, used, free, available, buffer, cache)
  - System load averages (1min, 5min, 15min)
  - Process counts (running, total, waiting)
  - Network bandwidth (RX/TX)
  - Disk I/O performance metrics
  - TCP/UDP connection statistics
  - Online user tracking

- **`DailyTraffic`** - Daily traffic aggregation by IP and node
- **`MonthlyTraffic`** - Monthly traffic summaries for long-term analysis

### 4. Access the Dashboard

Once configured, you can access the monitoring dashboard through your EasyAdmin interface:

- **Load Monitor**: `/admin/load-monitor/{nodeId}` - CPU, memory, and process monitoring
- **Network Monitor**: `/admin/network-monitor/{nodeId}` - Network traffic analysis with charts

## API Endpoints

### Load Conditions

Get server load conditions:

```text
GET /api/load-conditions/{nodeId}
```

Response:
```json
{
  "avgCpuUsage": 45.2,
  "avgLoad": 2.1,
  "avgMemUsage": 68.5,
  "avgProcess": 120
}
```

## Configuration

The bundle automatically configures itself through dependency injection. No additional configuration is required.

### Data Retention

The bundle includes automatic data cleanup for `MinuteStat` entities:

- **Default retention**: 60 days
- **Cleanup schedule**: Daily at 5:14 AM
- **Environment variable**: `SERVER_NODE_STAT_PERSIST_DAY_NUM` to customize retention period

### Monitoring Dashboard

The bundle provides two main dashboard views:

1. **Load Monitor** (`/admin/load-monitor/{nodeId}`):
    - CPU usage charts (user, system, stolen, idle)
    - Memory utilization graphs
    - System load averages
    - Process count monitoring
    - Summary cards with average values

2. **Network Monitor** (`/admin/network-monitor/{nodeId}`):
    - 24-hour bandwidth charts
    - 7-day traffic trends
    - RX/TX data visualization
    - Real-time network statistics

## Usage Examples

### Using the NodeMonitorService

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
        // Returns 24-hour and 7-day network monitoring data
        $data = $this->nodeMonitorService->getNetworkMonitorData($node);
        
        // Available data includes:
        // - labels24h: hourly labels for 24h chart
        // - rxData24h/txData24h: 24-hour bandwidth data
        // - labels7d: daily labels for 7-day chart 
        // - rxData7d/txData7d: 7-day bandwidth data
        return $data;
    }

    public function getLoadStats(Node $node): array
    {
        // Returns comprehensive load monitoring data
        $data = $this->nodeMonitorService->getLoadMonitorData($node);
        
        // Available data includes:
        // - CPU metrics: cpuUserData, cpuSystemData, cpuIdleData
        // - Load averages: loadOneData, loadFiveData, loadFifteenData
        // - Memory data: memoryTotalData, memoryUsedData, memoryFreeData
        // - Process data: processRunningData, processTotalData
        // - Summary averages: avgCpuUsage, avgLoad, avgMemUsage, avgProcess
        return $data;
    }
}
```

### Working with Repositories

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
        // Get recent minute-level statistics
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

## Advanced Usage

### Custom Data Collection

You can extend the bundle's data collection capabilities by creating custom collectors:

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
        
        // Set custom metrics
        $stat->setCpuUserPercent($this->getCpuUsage());
        $stat->setMemoryUsed($this->getMemoryUsage());
        // ... other metrics
        
        $this->entityManager->persist($stat);
        $this->entityManager->flush();
    }

    private function getCpuUsage(): float
    {
        // Your custom CPU collection logic
        return 0.0;
    }

    private function getMemoryUsage(): int
    {
        // Your custom memory collection logic
        return 0;
    }
}
```

### Data Aggregation

For performance optimization, you can create custom data aggregators:

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
        
        // Create daily aggregation
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

### Custom Monitoring Dashboard

Create custom dashboard controllers for specialized monitoring views:

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
        
        // Add custom calculations
        $data['performanceScore'] = $this->calculatePerformanceScore($data);
        
        return $this->render('admin/custom_performance_monitor.html.twig', [
            'node' => $node,
            'data' => $data,
        ]);
    }
    
    private function calculatePerformanceScore(array $data): float
    {
        // Custom performance scoring logic
        $cpuScore = 100 - $data['avgCpuUsage'];
        $memScore = 100 - $data['avgMemUsage'];
        $loadScore = max(0, 100 - ($data['avgLoad'] * 50));
        
        return ($cpuScore + $memScore + $loadScore) / 3;
    }
}
```

## Contributing

Contributions are welcome! Please follow these guidelines:

1. **Fork the repository** and create your feature branch from `master`
2. **Write tests** for your changes using PHPUnit
3. **Follow code standards** - use PHPStan and coding style guidelines
4. **Update documentation** if you're adding new features
5. **Submit a pull request** with a clear description of your changes

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit packages/server-stats-bundle/tests

# Run PHPStan analysis
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/server-stats-bundle

# Run package checks
bin/console app:check-packages server-stats-bundle -o -f
```

### Code Quality

Please ensure your code passes all quality checks:
- All tests must pass
- PHPStan analysis must be clean (level 9)
- Follow PSR-12 coding standards
- Add proper type declarations

## License

This bundle is released under the MIT License. See the bundled LICENSE file for details.