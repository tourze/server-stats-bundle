<?php

namespace ServerStatsBundle\Service;

use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Repository\MinuteStatRepository;

class NodeMonitorService
{
    public function __construct(
        private readonly MinuteStatRepository $minuteStatRepository,
    ) {
    }

    /**
     * 获取网络监控数据
     * @return array<string, mixed>
     */
    public function getNetworkMonitorData(Node $node): array
    {
        $now = new \DateTime();

        $network24h = $this->get24HourNetworkData($node, $now);
        $network7d = $this->get7DayNetworkData($node, $now);

        return [
            'labels24h' => $network24h['labels'],
            'rxData24h' => $network24h['rxData'],
            'txData24h' => $network24h['txData'],
            'labels7d' => $network7d['labels'],
            'rxData7d' => $network7d['rxData'],
            'txData7d' => $network7d['txData'],
        ];
    }

    /**
     * 获取负载监控数据
     * @return array<string, mixed>
     */
    public function getLoadMonitorData(Node $node): array
    {
        $now = new \DateTime();
        $startTime = (clone $now)->modify('-24 hours');

        $stats = $this->getLoadStats($node, $startTime, $now);
        $hourlyData = $this->aggregateLoadStatsByHour($stats);

        return $this->processHourlyLoadData($hourlyData);
    }

    /**
     * @param Node $node
     * @param \DateTime $startTime
     * @param \DateTime $endTime
     * @return array<int, mixed>
     */
    private function getLoadStats(Node $node, \DateTime $startTime, \DateTime $endTime): array
    {
        $qb = $this->minuteStatRepository->createQueryBuilder('s')
            ->where('s.node = :node')
            ->andWhere('s.datetime >= :startTime')
            ->andWhere('s.datetime <= :endTime')
            ->setParameter('node', $node)
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime)
            ->orderBy('s.datetime', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array<int, mixed> $stats
     * @return array<string, mixed>
     */
    private function aggregateLoadStatsByHour(array $stats): array
    {
        $hourlyData = [];
        foreach ($stats as $stat) {
            $hourKey = $stat->getDatetime()->format('Y-m-d H:00');

            if (!isset($hourlyData[$hourKey])) {
                $hourlyData[$hourKey] = $this->createEmptyHourData($stat);
            }

            $hourlyData[$hourKey] = $this->collectStatData($hourlyData[$hourKey], $stat);
            ++$hourlyData[$hourKey]['count'];
        }

        return $hourlyData;
    }

    /**
     * @param mixed $stat
     * @return array<string, mixed>
     */
    private function createEmptyHourData($stat): array
    {
        return [
            'label' => $stat->getDatetime()->format('H:i'),
            'count' => 0,
            'cpuUser' => 0,
            'cpuSystem' => 0,
            'cpuStolen' => 0,
            'cpuIdle' => 0,
            'loadOne' => 0,
            'loadFive' => 0,
            'loadFifteen' => 0,
            'memoryTotal' => 0,
            'memoryUsed' => 0,
            'memoryFree' => 0,
            'memoryAvailable' => 0,
            'processRunning' => 0,
            'processTotal' => 0,
        ];
    }

    /**
     * @param array<string, mixed> $hourData
     * @param mixed $stat
     */
    /**
     * @param array<string, mixed> $hourData
     * @param mixed $stat
     * @return array<string, mixed>
     */
    private function collectStatData(array $hourData, $stat): array
    {
        $hourData = $this->collectCpuData($hourData, $stat);
        $hourData = $this->collectLoadData($hourData, $stat);
        $hourData = $this->collectMemoryData($hourData, $stat);

        return $this->collectProcessData($hourData, $stat);
    }

    /**
     * @param array<string, mixed> $hourData
     * @param mixed $stat
     */
    /**
     * @param array<string, mixed> $hourData
     * @param mixed $stat
     * @return array<string, mixed>
     */
    private function collectCpuData(array $hourData, $stat): array
    {
        if (null !== $stat->getCpuUserPercent()) {
            $hourData['cpuUser'] += $stat->getCpuUserPercent();
        }
        if (null !== $stat->getCpuSystemPercent()) {
            $hourData['cpuSystem'] += $stat->getCpuSystemPercent();
        }
        if (null !== $stat->getCpuStolenPercent()) {
            $hourData['cpuStolen'] += $stat->getCpuStolenPercent();
        }
        if (null !== $stat->getCpuIdlePercent()) {
            $hourData['cpuIdle'] += $stat->getCpuIdlePercent();
        }

        return $hourData;
    }

    /**
     * @param array<string, mixed> $hourData
     * @param mixed $stat
     */
    /**
     * @param array<string, mixed> $hourData
     * @param mixed $stat
     * @return array<string, mixed>
     */
    private function collectLoadData(array $hourData, $stat): array
    {
        if (null !== $stat->getLoadOneMinute()) {
            $hourData['loadOne'] += (float) $stat->getLoadOneMinute();
        }
        if (null !== $stat->getLoadFiveMinutes()) {
            $hourData['loadFive'] += (float) $stat->getLoadFiveMinutes();
        }
        if (null !== $stat->getLoadFifteenMinutes()) {
            $hourData['loadFifteen'] += (float) $stat->getLoadFifteenMinutes();
        }

        return $hourData;
    }

    /**
     * @param array<string, mixed> $hourData
     * @param mixed $stat
     */
    /**
     * @param array<string, mixed> $hourData
     * @param mixed $stat
     * @return array<string, mixed>
     */
    private function collectMemoryData(array $hourData, $stat): array
    {
        if (null !== $stat->getMemoryTotal()) {
            $hourData['memoryTotal'] += $stat->getMemoryTotal();
        }
        if (null !== $stat->getMemoryUsed()) {
            $hourData['memoryUsed'] += $stat->getMemoryUsed();
        }
        if (null !== $stat->getMemoryFree()) {
            $hourData['memoryFree'] += $stat->getMemoryFree();
        }
        if (null !== $stat->getMemoryAvailable()) {
            $hourData['memoryAvailable'] += $stat->getMemoryAvailable();
        }

        return $hourData;
    }

    /**
     * @param array<string, mixed> $hourData
     * @param mixed $stat
     */
    /**
     * @param array<string, mixed> $hourData
     * @param mixed $stat
     * @return array<string, mixed>
     */
    private function collectProcessData(array $hourData, $stat): array
    {
        if (null !== $stat->getProcessRunning()) {
            $hourData['processRunning'] += $stat->getProcessRunning();
        }
        if (null !== $stat->getProcessTotal()) {
            $hourData['processTotal'] += $stat->getProcessTotal();
        }

        return $hourData;
    }

    /**
     * @param array<string, mixed> $hourlyData
     * @return array<string, mixed>
     */
    private function processHourlyLoadData(array $hourlyData): array
    {
        $result = $this->initializeResultArrays();
        $totalData = $this->initializeTotalData();

        foreach ($hourlyData as $data) {
            $count = $data['count'];
            if ($count > 0) {
                $result = $this->addHourlyDataToResult($result, $data, $count);
                $totalData = $this->addToTotalData($totalData, $data, $count);
            }
        }

        $averages = $this->calculateFinalAverages($totalData);

        return array_merge($result, $averages);
    }

    /**
     * @return array<string, mixed>
     */
    private function initializeResultArrays(): array
    {
        return [
            'labels' => [],
            'cpuUserData' => [],
            'cpuSystemData' => [],
            'cpuStolenData' => [],
            'cpuIdleData' => [],
            'loadOneData' => [],
            'loadFiveData' => [],
            'loadFifteenData' => [],
            'memoryTotalData' => [],
            'memoryUsedData' => [],
            'memoryFreeData' => [],
            'memoryAvailableData' => [],
            'processRunningData' => [],
            'processTotalData' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function initializeTotalData(): array
    {
        return [
            'cpuUsage' => 0,
            'loadAvg' => 0,
            'memUsage' => 0,
            'process' => 0,
            'count' => 0,
        ];
    }

    /**
     * @param array<string, mixed> $result
     * @param array<string, mixed> $data
     * @param int $count
     */
    /**
     * @param array<string, mixed> $result
     * @param array<string, mixed> $data
     * @param int $count
     * @return array<string, mixed>
     */
    private function addHourlyDataToResult(array $result, array $data, int $count): array
    {
        $result['labels'][] = $data['label'];
        $result['cpuUserData'][] = $data['cpuUser'] / $count;
        $result['cpuSystemData'][] = $data['cpuSystem'] / $count;
        $result['cpuStolenData'][] = $data['cpuStolen'] / $count;
        $result['cpuIdleData'][] = $data['cpuIdle'] / $count;
        $result['loadOneData'][] = $data['loadOne'] / $count;
        $result['loadFiveData'][] = $data['loadFive'] / $count;
        $result['loadFifteenData'][] = $data['loadFifteen'] / $count;
        $result['memoryTotalData'][] = $data['memoryTotal'] / $count;
        $result['memoryUsedData'][] = $data['memoryUsed'] / $count;
        $result['memoryFreeData'][] = $data['memoryFree'] / $count;
        $result['memoryAvailableData'][] = $data['memoryAvailable'] / $count;
        $result['processRunningData'][] = $data['processRunning'] / $count;
        $result['processTotalData'][] = $data['processTotal'] / $count;

        return $result;
    }

    /**
     * @param array<string, mixed> $totalData
     * @param array<string, mixed> $data
     * @param int $count
     */
    /**
     * @param array<string, mixed> $totalData
     * @param array<string, mixed> $data
     * @param int $count
     * @return array<string, mixed>
     */
    private function addToTotalData(array $totalData, array $data, int $count): array
    {
        $totalData['cpuUsage'] += (($data['cpuUser'] + $data['cpuSystem'] + $data['cpuStolen']) / $count);
        $totalData['loadAvg'] += ($data['loadOne'] / $count);

        if ($data['memoryTotal'] > 0) {
            $memUsage = ($data['memoryUsed'] / $data['memoryTotal']) * 100;
            $totalData['memUsage'] += $memUsage;
        }

        $totalData['process'] += ($data['processTotal'] / $count);
        ++$totalData['count'];

        return $totalData;
    }

    /**
     * @param array<string, mixed> $totalData
     * @return array<string, mixed>
     */
    private function calculateFinalAverages(array $totalData): array
    {
        $count = $totalData['count'];

        return [
            'avgCpuUsage' => $count > 0 ? $totalData['cpuUsage'] / $count : 0,
            'avgLoad' => $count > 0 ? $totalData['loadAvg'] / $count : 0,
            'avgMemUsage' => $count > 0 ? $totalData['memUsage'] / $count : 0,
            'avgProcess' => $count > 0 ? $totalData['process'] / $count : 0,
        ];
    }

    /**
     * @param Node $node
     * @param \DateTime $now
     * @return array<string, mixed>
     */
    private function get24HourNetworkData(Node $node, \DateTime $now): array
    {
        $startTime = (clone $now)->modify('-24 hours');
        $timeSlots = $this->generate24HourSlots($startTime);

        $stats = $this->getNetworkStats($node, $startTime, $now);
        $hourlyData = $this->aggregateByHour($stats);

        return $this->fillNetworkData($timeSlots, $hourlyData, 'H:i', 'H');
    }

    /**
     * @param Node $node
     * @param \DateTime $now
     * @return array<string, mixed>
     */
    private function get7DayNetworkData(Node $node, \DateTime $now): array
    {
        $startTime = (clone $now)->modify('-7 days')->setTime(0, 0, 0);
        $timeSlots = $this->generate7DaySlots($startTime);

        $stats = $this->getNetworkStats($node, $startTime, $now);
        $dailyData = $this->aggregateByDay($stats);

        return $this->fillNetworkData($timeSlots, $dailyData, 'm-d', 'm-d');
    }

    /**
     * @param \DateTime $startTime
     * @return array<int, \DateTime>
     */
    private function generate24HourSlots(\DateTime $startTime): array
    {
        $slots = [];
        for ($i = 0; $i < 24; ++$i) {
            $slots[] = (clone $startTime)->modify("+{$i} hours");
        }

        return $slots;
    }

    /**
     * @param \DateTime $startTime
     * @return array<int, \DateTime>
     */
    private function generate7DaySlots(\DateTime $startTime): array
    {
        $slots = [];
        for ($i = 0; $i < 7; ++$i) {
            $slots[] = (clone $startTime)->modify("+{$i} days");
        }

        return $slots;
    }

    /**
     * @param Node $node
     * @param \DateTime $startTime
     * @param \DateTime $endTime
     * @return array<int, mixed>
     */
    private function getNetworkStats(Node $node, \DateTime $startTime, \DateTime $endTime): array
    {
        $qb = $this->minuteStatRepository->createQueryBuilder('s')
            ->where('s.node = :node')
            ->andWhere('s.datetime >= :startTime')
            ->andWhere('s.datetime <= :endTime')
            ->setParameter('node', $node)
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime)
            ->orderBy('s.datetime', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array<int, mixed> $stats
     * @return array<string, mixed>
     */
    private function aggregateByHour(array $stats): array
    {
        $aggregated = [];
        foreach ($stats as $stat) {
            $key = $stat->getDatetime()->format('H');
            $aggregated = $this->addNetworkDataToAggregate($aggregated, $key, $stat);
        }

        return $aggregated;
    }

    /**
     * @param array<int, mixed> $stats
     * @return array<string, mixed>
     */
    private function aggregateByDay(array $stats): array
    {
        $aggregated = [];
        foreach ($stats as $stat) {
            $key = $stat->getDatetime()->format('m-d');
            $aggregated = $this->addNetworkDataToAggregate($aggregated, $key, $stat);
        }

        return $aggregated;
    }

    /**
     * @param array<string, mixed> $aggregated
     * @param string $key
     * @param mixed $stat
     */
    /**
     * @param array<string, mixed> $aggregated
     * @param string $key
     * @param mixed $stat
     * @return array<string, mixed>
     */
    private function addNetworkDataToAggregate(array $aggregated, string $key, $stat): array
    {
        if (!isset($aggregated[$key])) {
            $aggregated[$key] = ['rx' => 0, 'tx' => 0, 'count' => 0];
        }

        $rx = $stat->getRxBandwidth() ? (int) $stat->getRxBandwidth() : 0;
        $tx = $stat->getTxBandwidth() ? (int) $stat->getTxBandwidth() : 0;

        $aggregated[$key]['rx'] += $rx;
        $aggregated[$key]['tx'] += $tx;
        ++$aggregated[$key]['count'];

        return $aggregated;
    }

    /**
     * @param array<int, \DateTime> $timeSlots
     * @param array<string, mixed> $aggregatedData
     * @param string $labelFormat
     * @param string $keyFormat
     * @return array<string, mixed>
     */
    private function fillNetworkData(array $timeSlots, array $aggregatedData, string $labelFormat, string $keyFormat): array
    {
        $labels = [];
        $rxData = [];
        $txData = [];

        foreach ($timeSlots as $slot) {
            $labels[] = $slot->format($labelFormat);
            $key = $slot->format($keyFormat);

            if (isset($aggregatedData[$key]) && $aggregatedData[$key]['count'] > 0) {
                $rxData[] = $aggregatedData[$key]['rx'] / $aggregatedData[$key]['count'];
                $txData[] = $aggregatedData[$key]['tx'] / $aggregatedData[$key]['count'];
            } else {
                $rxData[] = 0;
                $txData[] = 0;
            }
        }

        return [
            'labels' => $labels,
            'rxData' => $rxData,
            'txData' => $txData,
        ];
    }
}
