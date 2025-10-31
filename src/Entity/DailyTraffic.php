<?php

namespace ServerStatsBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Repository\DailyTrafficRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\UniqueColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity(repositoryClass: DailyTrafficRepository::class)]
#[ORM\Table(name: 'ims_server_node_daily_traffic', options: ['comment' => '服务器流量'])]
#[ORM\UniqueConstraint(name: 'ims_daily_traffic_node_date_idx_unique', columns: ['node_id', 'summary_date'])]
class DailyTraffic implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Node $node = null;

    #[Assert\NotBlank]
    #[Assert\Ip]
    #[Assert\Length(max: 45)]
    #[ORM\Column(length: 45, options: ['comment' => 'IP地址'])]
    private string $ip;

    #[Assert\NotNull]
    #[ORM\Column(name: 'summary_date', type: Types::DATE_IMMUTABLE, options: ['comment' => '统计日期'])]
    private \DateTimeInterface $date;

    #[Assert\PositiveOrZero]
    #[Assert\Regex(pattern: '/^\d+$/', message: 'TX must be a positive integer')]
    #[Assert\Length(max: 19)]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'TX'])]
    private ?string $tx = null;

    #[Assert\PositiveOrZero]
    #[Assert\Regex(pattern: '/^\d+$/', message: 'RX must be a positive integer')]
    #[Assert\Length(max: 19)]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'RX'])]
    private ?string $rx = null;

    public function __toString(): string
    {
        return sprintf('DailyTraffic[%s] %s - %s',
            $this->id,
            $this->ip,
            $this->date->format('Y-m-d')
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNode(): ?Node
    {
        return $this->node;
    }

    public function setNode(?Node $node): void
    {
        $this->node = $node;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): void
    {
        $this->date = $date;
    }

    public function getTx(): ?string
    {
        return $this->tx;
    }

    public function setTx(string $tx): void
    {
        $this->tx = $tx;
    }

    public function getRx(): ?string
    {
        return $this->rx;
    }

    public function setRx(string $rx): void
    {
        $this->rx = $rx;
    }
}
