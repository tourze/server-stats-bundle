<?php

namespace ServerStatsBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Repository\MonthlyTrafficRepository;
use Tourze\DoctrineIndexedBundle\Attribute\UniqueColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity(repositoryClass: MonthlyTrafficRepository::class)]
#[ORM\Table(name: 'ims_server_node_monthly_traffic', options: ['comment' => '节点月流量记录'])]
class MonthlyTraffic implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[UniqueColumn]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Node $node = null;

    #[ORM\Column(length: 45, options: ['comment' => 'IP地址'])]
    private string $ip;

    #[UniqueColumn]
    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '月份'])]
    private string $month;

    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'TX'])]
    private ?string $tx = null;

    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'RX'])]
    private ?string $rx = null;

    public function __toString(): string
    {
        return sprintf('MonthlyTraffic[%s] %s - %s', 
            $this->id, 
            $this->ip, 
            $this->month
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

    public function setIp(string $ip): static
    {
        $this->ip = $ip;

        return $this;
    }

    public function getMonth(): string
    {
        return $this->month;
    }

    public function setMonth(string $month): static
    {
        $this->month = $month;

        return $this;
    }

    public function getTx(): ?string
    {
        return $this->tx;
    }

    public function setTx(string $tx): static
    {
        $this->tx = $tx;

        return $this;
    }

    public function getRx(): ?string
    {
        return $this->rx;
    }

    public function setRx(string $rx): static
    {
        $this->rx = $rx;

        return $this;
    }
}
