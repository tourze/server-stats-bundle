<?php

namespace ServerStatsBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Repository\MinuteStatRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\CreateTimeAware;
use Tourze\ScheduleEntityCleanBundle\Attribute\AsScheduleClean;

/**
 * @implements AdminArrayInterface<string, mixed>
 */
#[AsScheduleClean(expression: '14 5 * * *', defaultKeepDay: 60, keepDayEnv: 'SERVER_NODE_STAT_PERSIST_DAY_NUM')]
#[ORM\Table(name: 'ims_server_node_stat', options: ['comment' => '节点统计'])]
#[ORM\Entity(repositoryClass: MinuteStatRepository::class)]
#[ORM\UniqueConstraint(name: 'ims_server_node_stat_idx_unique', columns: ['node_id', 'datetime'])]
class MinuteStat implements AdminArrayInterface, \Stringable
{
    use CreateTimeAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Node $node;

    #[Assert\NotNull]
    #[IndexColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '时间'])]
    private ?\DateTimeInterface $datetime = null;

    #[Assert\Range(min: 0, max: 100)]
    #[ORM\Column(nullable: true, options: ['comment' => '系统CPU百分比'])]
    private ?int $cpuSystemPercent = null;

    #[Assert\Range(min: 0, max: 100)]
    #[ORM\Column(nullable: true, options: ['comment' => '用户CPU百分比'])]
    private ?int $cpuUserPercent = null;

    #[Assert\Range(min: 0, max: 100)]
    #[ORM\Column(nullable: true, options: ['comment' => '被偷CPU百分比'])]
    private ?int $cpuStolenPercent = null;

    #[Assert\Range(min: 0, max: 100)]
    #[ORM\Column(nullable: true, options: ['comment' => '空闲CPU百分比'])]
    private ?int $cpuIdlePercent = null;

    #[Assert\PositiveOrZero]
    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'Load must be a positive decimal with up to 2 decimal places')]
    #[Assert\Length(max: 5)]
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true, options: ['comment' => '过去1分钟负载'])]
    private ?string $loadOneMinute = null;

    #[Assert\PositiveOrZero]
    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'Load must be a positive decimal with up to 2 decimal places')]
    #[Assert\Length(max: 5)]
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true, options: ['comment' => '过去5分钟负载'])]
    private ?string $loadFiveMinutes = null;

    #[Assert\PositiveOrZero]
    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'Load must be a positive decimal with up to 2 decimal places')]
    #[Assert\Length(max: 5)]
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true, options: ['comment' => '过去15分钟负载'])]
    private ?string $loadFifteenMinutes = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => '运行进程数'])]
    private ?int $processRunning = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => '总进程数'])]
    private ?int $processTotal = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => '不可中断睡眠进程数'])]
    private ?int $processUninterruptibleSleep = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => '等待运行进程数'])]
    private ?int $processWaitingForRun = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => '总内存'])]
    private ?int $memoryTotal = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => '已用内存'])]
    private ?int $memoryUsed = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => '空闲内存'])]
    private ?int $memoryFree = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => '可用内存'])]
    private ?int $memoryAvailable = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => '缓冲区内存'])]
    private ?int $memoryBuffer = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => '缓存内存'])]
    private ?int $memoryCache = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => '共享内存'])]
    private ?int $memoryShared = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => '交换区已用'])]
    private ?int $memorySwapUsed = null;

    #[Assert\PositiveOrZero]
    #[Assert\Regex(pattern: '/^\d+$/', message: 'Bandwidth must be a positive integer')]
    #[Assert\Length(max: 19)]
    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['comment' => '入带宽'])]
    private ?string $rxBandwidth = null;

    #[Assert\PositiveOrZero]
    #[Assert\Regex(pattern: '/^\d+$/', message: 'Packets must be a positive integer')]
    #[Assert\Length(max: 19)]
    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['comment' => '入包量'])]
    private ?string $rxPackets = null;

    #[Assert\PositiveOrZero]
    #[Assert\Regex(pattern: '/^\d+$/', message: 'Bandwidth must be a positive integer')]
    #[Assert\Length(max: 19)]
    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['comment' => '出带宽'])]
    private ?string $txBandwidth = null;

    #[Assert\PositiveOrZero]
    #[Assert\Regex(pattern: '/^\d+$/', message: 'Packets must be a positive integer')]
    #[Assert\Length(max: 19)]
    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['comment' => '出包量'])]
    private ?string $txPackets = null;

    #[Assert\PositiveOrZero]
    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'IOPS must be a positive decimal')]
    #[Assert\Length(max: 23)]
    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true, options: ['comment' => '磁盘读IOPS'])]
    private ?string $diskReadIops = null;

    #[Assert\PositiveOrZero]
    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'IOPS must be a positive decimal')]
    #[Assert\Length(max: 23)]
    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true, options: ['comment' => '磁盘写IOPS'])]
    private ?string $diskWriteIops = null;

    #[Assert\PositiveOrZero]
    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'IO wait time must be a positive decimal')]
    #[Assert\Length(max: 13)]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '磁盘IO等待时间'])]
    private ?string $diskIoWait = null;

    #[Assert\PositiveOrZero]
    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'Average IO time must be a positive decimal')]
    #[Assert\Length(max: 13)]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '磁盘平均IO时间'])]
    private ?string $diskAvgIoTime = null;

    #[Assert\Range(min: 0, max: 100)]
    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'Busy percent must be a positive decimal')]
    #[Assert\Length(max: 6)]
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true, options: ['comment' => '磁盘繁忙度'])]
    private ?string $diskBusyPercent = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => 'TCP连接数'])]
    private ?int $tcpEstab = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => 'TCP监听数'])]
    private ?int $tcpListen = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => 'TCP SYN已发送'])]
    private ?int $tcpSynSent = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => 'TCP SYN已接收'])]
    private ?int $tcpSynRecv = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => 'TCP FIN_WAIT1状态'])]
    private ?int $tcpFinWait1 = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => 'TCP FIN_WAIT2状态'])]
    private ?int $tcpFinWait2 = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => 'TCP TIME_WAIT状态'])]
    private ?int $tcpTimeWait = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => 'TCP CLOSE_WAIT状态'])]
    private ?int $tcpCloseWait = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => 'TCP CLOSING状态'])]
    private ?int $tcpClosing = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => 'TCP LAST_ACK状态'])]
    private ?int $tcpLastAck = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => 'UDP监听数'])]
    private ?int $udpCount = null;

    /** @var array<mixed>|null */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '在线用户'])]
    private ?array $onlineUsers = null;

    public function __toString(): string
    {
        return sprintf('MinuteStat[%s] Node: %s - %s',
            $this->id,
            $this->node->getId() ?? 'Unknown',
            $this->datetime?->format('Y-m-d H:i:s')
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function setNode(Node $node): void
    {
        $this->node = $node;
    }

    public function getDatetime(): ?\DateTimeInterface
    {
        return $this->datetime;
    }

    public function setDatetime(\DateTimeInterface $datetime): void
    {
        $this->datetime = $datetime;
    }

    public function getCpuSystemPercent(): ?int
    {
        return $this->cpuSystemPercent;
    }

    public function setCpuSystemPercent(?int $cpuSystemPercent): void
    {
        $this->cpuSystemPercent = $cpuSystemPercent;
    }

    public function getCpuUserPercent(): ?int
    {
        return $this->cpuUserPercent;
    }

    public function setCpuUserPercent(?int $cpuUserPercent): void
    {
        $this->cpuUserPercent = $cpuUserPercent;
    }

    public function getCpuStolenPercent(): ?int
    {
        return $this->cpuStolenPercent;
    }

    public function setCpuStolenPercent(?int $cpuStolenPercent): void
    {
        $this->cpuStolenPercent = $cpuStolenPercent;
    }

    public function getCpuIdlePercent(): ?int
    {
        return $this->cpuIdlePercent;
    }

    public function setCpuIdlePercent(?int $cpuIdlePercent): void
    {
        $this->cpuIdlePercent = $cpuIdlePercent;
    }

    public function getLoadOneMinute(): ?string
    {
        return $this->loadOneMinute;
    }

    public function setLoadOneMinute(?string $loadOneMinute): void
    {
        $this->loadOneMinute = $loadOneMinute;
    }

    public function getLoadFiveMinutes(): ?string
    {
        return $this->loadFiveMinutes;
    }

    public function setLoadFiveMinutes(?string $loadFiveMinutes): void
    {
        $this->loadFiveMinutes = $loadFiveMinutes;
    }

    public function getLoadFifteenMinutes(): ?string
    {
        return $this->loadFifteenMinutes;
    }

    public function setLoadFifteenMinutes(?string $loadFifteenMinutes): void
    {
        $this->loadFifteenMinutes = $loadFifteenMinutes;
    }

    public function getProcessRunning(): ?int
    {
        return $this->processRunning;
    }

    public function setProcessRunning(?int $processRunning): void
    {
        $this->processRunning = $processRunning;
    }

    public function getProcessTotal(): ?int
    {
        return $this->processTotal;
    }

    public function setProcessTotal(?int $processTotal): void
    {
        $this->processTotal = $processTotal;
    }

    public function getProcessUninterruptibleSleep(): ?int
    {
        return $this->processUninterruptibleSleep;
    }

    public function setProcessUninterruptibleSleep(?int $processUninterruptibleSleep): void
    {
        $this->processUninterruptibleSleep = $processUninterruptibleSleep;
    }

    public function getProcessWaitingForRun(): ?int
    {
        return $this->processWaitingForRun;
    }

    public function setProcessWaitingForRun(?int $processWaitingForRun): void
    {
        $this->processWaitingForRun = $processWaitingForRun;
    }

    public function getMemoryTotal(): ?int
    {
        return $this->memoryTotal;
    }

    public function setMemoryTotal(?int $memoryTotal): void
    {
        $this->memoryTotal = $memoryTotal;
    }

    public function getMemoryUsed(): ?int
    {
        return $this->memoryUsed;
    }

    public function setMemoryUsed(?int $memoryUsed): void
    {
        $this->memoryUsed = $memoryUsed;
    }

    public function getMemoryFree(): ?int
    {
        return $this->memoryFree;
    }

    public function setMemoryFree(?int $memoryFree): void
    {
        $this->memoryFree = $memoryFree;
    }

    public function getMemoryAvailable(): ?int
    {
        return $this->memoryAvailable;
    }

    public function setMemoryAvailable(?int $memoryAvailable): void
    {
        $this->memoryAvailable = $memoryAvailable;
    }

    public function getMemoryBuffer(): ?int
    {
        return $this->memoryBuffer;
    }

    public function setMemoryBuffer(?int $memoryBuffer): void
    {
        $this->memoryBuffer = $memoryBuffer;
    }

    public function getMemoryCache(): ?int
    {
        return $this->memoryCache;
    }

    public function setMemoryCache(?int $memoryCache): void
    {
        $this->memoryCache = $memoryCache;
    }

    public function getMemoryShared(): ?int
    {
        return $this->memoryShared;
    }

    public function setMemoryShared(?int $memoryShared): void
    {
        $this->memoryShared = $memoryShared;
    }

    public function getMemorySwapUsed(): ?int
    {
        return $this->memorySwapUsed;
    }

    public function setMemorySwapUsed(?int $memorySwapUsed): void
    {
        $this->memorySwapUsed = $memorySwapUsed;
    }

    public function getRxBandwidth(): ?string
    {
        return $this->rxBandwidth;
    }

    public function setRxBandwidth(?string $rxBandwidth): void
    {
        $this->rxBandwidth = $rxBandwidth;
    }

    public function getRxPackets(): ?string
    {
        return $this->rxPackets;
    }

    public function setRxPackets(?string $rxPackets): void
    {
        $this->rxPackets = $rxPackets;
    }

    public function getTxBandwidth(): ?string
    {
        return $this->txBandwidth;
    }

    public function setTxBandwidth(?string $txBandwidth): void
    {
        $this->txBandwidth = $txBandwidth;
    }

    public function getTxPackets(): ?string
    {
        return $this->txPackets;
    }

    public function setTxPackets(?string $txPackets): void
    {
        $this->txPackets = $txPackets;
    }

    public function getDiskReadIops(): ?string
    {
        return $this->diskReadIops;
    }

    public function setDiskReadIops(?string $diskReadIops): void
    {
        $this->diskReadIops = $diskReadIops;
    }

    public function getDiskWriteIops(): ?string
    {
        return $this->diskWriteIops;
    }

    public function setDiskWriteIops(?string $diskWriteIops): void
    {
        $this->diskWriteIops = $diskWriteIops;
    }

    public function getDiskIoWait(): ?string
    {
        return $this->diskIoWait;
    }

    public function setDiskIoWait(?string $diskIoWait): void
    {
        $this->diskIoWait = $diskIoWait;
    }

    public function getDiskAvgIoTime(): ?string
    {
        return $this->diskAvgIoTime;
    }

    public function setDiskAvgIoTime(?string $diskAvgIoTime): void
    {
        $this->diskAvgIoTime = $diskAvgIoTime;
    }

    public function getDiskBusyPercent(): ?string
    {
        return $this->diskBusyPercent;
    }

    public function setDiskBusyPercent(?string $diskBusyPercent): void
    {
        $this->diskBusyPercent = $diskBusyPercent;
    }

    public function getTcpEstab(): ?int
    {
        return $this->tcpEstab;
    }

    public function setTcpEstab(?int $tcpEstab): void
    {
        $this->tcpEstab = $tcpEstab;
    }

    public function getTcpListen(): ?int
    {
        return $this->tcpListen;
    }

    public function setTcpListen(?int $tcpListen): void
    {
        $this->tcpListen = $tcpListen;
    }

    public function getTcpSynSent(): ?int
    {
        return $this->tcpSynSent;
    }

    public function setTcpSynSent(?int $tcpSynSent): void
    {
        $this->tcpSynSent = $tcpSynSent;
    }

    public function getTcpSynRecv(): ?int
    {
        return $this->tcpSynRecv;
    }

    public function setTcpSynRecv(?int $tcpSynRecv): void
    {
        $this->tcpSynRecv = $tcpSynRecv;
    }

    public function getTcpFinWait1(): ?int
    {
        return $this->tcpFinWait1;
    }

    public function setTcpFinWait1(?int $tcpFinWait1): void
    {
        $this->tcpFinWait1 = $tcpFinWait1;
    }

    public function getTcpFinWait2(): ?int
    {
        return $this->tcpFinWait2;
    }

    public function setTcpFinWait2(?int $tcpFinWait2): void
    {
        $this->tcpFinWait2 = $tcpFinWait2;
    }

    public function getTcpTimeWait(): ?int
    {
        return $this->tcpTimeWait;
    }

    public function setTcpTimeWait(?int $tcpTimeWait): void
    {
        $this->tcpTimeWait = $tcpTimeWait;
    }

    public function getTcpCloseWait(): ?int
    {
        return $this->tcpCloseWait;
    }

    public function setTcpCloseWait(?int $tcpCloseWait): void
    {
        $this->tcpCloseWait = $tcpCloseWait;
    }

    public function getTcpClosing(): ?int
    {
        return $this->tcpClosing;
    }

    public function setTcpClosing(?int $tcpClosing): void
    {
        $this->tcpClosing = $tcpClosing;
    }

    public function getTcpLastAck(): ?int
    {
        return $this->tcpLastAck;
    }

    public function setTcpLastAck(?int $tcpLastAck): void
    {
        $this->tcpLastAck = $tcpLastAck;
    }

    public function getUdpCount(): ?int
    {
        return $this->udpCount;
    }

    public function setUdpCount(?int $udpCount): void
    {
        $this->udpCount = $udpCount;
    }

    /**
     * @return array<mixed>|null
     */
    public function getOnlineUsers(): ?array
    {
        return $this->onlineUsers;
    }

    /**
     * @param array<mixed>|null $onlineUsers
     */
    public function setOnlineUsers(?array $onlineUsers): void
    {
        $this->onlineUsers = $onlineUsers;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'datetime' => $this->getDatetime()?->format('Y-m-d H:i:s'),
            'cpuSystemPercent' => $this->getCpuSystemPercent(),
            'cpuUserPercent' => $this->getCpuUserPercent(),
            'cpuStolenPercent' => $this->getCpuStolenPercent(),
            'cpuIdlePercent' => $this->getCpuIdlePercent(),
            'loadOneMinute' => $this->getLoadOneMinute(),
            'loadFiveMinutes' => $this->getLoadFiveMinutes(),
            'loadFifteenMinutes' => $this->getLoadFifteenMinutes(),
            'processRunning' => $this->getProcessRunning(),
            'processTotal' => $this->getProcessTotal(),
            'processUninterruptibleSleep' => $this->getProcessUninterruptibleSleep(),
            'processWaitingForRun' => $this->getProcessWaitingForRun(),
            'memoryTotal' => $this->getMemoryTotal(),
            'memoryUsed' => $this->getMemoryUsed(),
            'memoryFree' => $this->getMemoryFree(),
            'memoryAvailable' => $this->getMemoryAvailable(),
            'rxBandwidth' => $this->getRxBandwidth(),
            'rxPackets' => $this->getRxPackets(),
            'txBandwidth' => $this->getTxBandwidth(),
            'txPackets' => $this->getTxPackets(),
            'tcpEstab' => $this->getTcpEstab(),
            'tcpListen' => $this->getTcpListen(),
            'udpCount' => $this->getUdpCount(),
        ];
    }
}
