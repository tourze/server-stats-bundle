<?php

namespace ServerStatsBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Repository\DailyTrafficRepository;
use Tourze\DoctrineIndexedBundle\Attribute\UniqueColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity(repositoryClass: DailyTrafficRepository::class)]
#[ORM\Table(name: 'ims_server_node_daily_traffic', options: ['comment' => '服务器流量'])]
class DailyTraffic implements \Stringable
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
    #[ORM\Column(name: 'summary_date', type: Types::DATE_MUTABLE)]
    private \DateTimeInterface $date;

    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'TX'])]
    private ?string $tx = null;

    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'RX'])]
    private ?string $rx = null;

    public function __toString(): string
    {
        return sprintf('DailyTraffic[%s] %s - %s', 
            $this->id, 
            $this->ip, 
            $this->date?->format('Y-m-d')
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

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

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
