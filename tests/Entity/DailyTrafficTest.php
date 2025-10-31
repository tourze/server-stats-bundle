<?php

namespace ServerStatsBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Entity\DailyTraffic;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(DailyTraffic::class)]
final class DailyTrafficTest extends AbstractEntityTestCase
{
    private DailyTraffic $dailyTraffic;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dailyTraffic = new DailyTraffic();
    }

    protected function createEntity(): object
    {
        return new DailyTraffic();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'node' => ['node', null];
        yield 'ip' => ['ip', '192.168.1.1'];
        yield 'date' => ['date', new \DateTime()];
        yield 'tx' => ['tx', '1024'];
        yield 'rx' => ['rx', '2048'];
    }

    public function testIdInitiallyNull(): void
    {
        $this->assertNull($this->dailyTraffic->getId());
    }

    public function testNodeGetterAndSetter(): void
    {
        $node = new Node();

        $this->dailyTraffic->setNode($node);
        $this->assertSame($node, $this->dailyTraffic->getNode());

        // 测试设置为 null
        $this->dailyTraffic->setNode(null);
        $this->assertNull($this->dailyTraffic->getNode());
    }

    public function testIpGetterAndSetter(): void
    {
        $ip = '192.168.1.100';

        $this->dailyTraffic->setIp($ip);
        $this->assertSame($ip, $this->dailyTraffic->getIp());
    }

    public function testIpWithDifferentFormats(): void
    {
        // IPv4
        $ipv4 = '192.168.1.1';
        $this->dailyTraffic->setIp($ipv4);
        $this->assertSame($ipv4, $this->dailyTraffic->getIp());

        // IPv6
        $ipv6 = '2001:db8::1';
        $this->dailyTraffic->setIp($ipv6);
        $this->assertSame($ipv6, $this->dailyTraffic->getIp());

        // 边界测试 - 最长IP地址
        $longIp = '255.255.255.255';
        $this->dailyTraffic->setIp($longIp);
        $this->assertSame($longIp, $this->dailyTraffic->getIp());
    }

    public function testDateGetterAndSetter(): void
    {
        $date = new \DateTime('2023-01-01');

        $this->dailyTraffic->setDate($date);
        $this->assertSame($date, $this->dailyTraffic->getDate());
    }

    public function testDateWithDifferentFormats(): void
    {
        // 测试不同日期格式
        $dates = [
            new \DateTime('2023-01-01'),
            new \DateTime('2023-12-31'),
            new \DateTime('2024-02-29'), // 闰年
            new \DateTimeImmutable('2023-06-15'),
        ];

        foreach ($dates as $date) {
            $this->dailyTraffic->setDate($date);
            $this->assertEquals($date->format('Y-m-d'), $this->dailyTraffic->getDate()->format('Y-m-d'));
        }
    }

    public function testTxGetterAndSetter(): void
    {
        $tx = '1024000';

        $this->dailyTraffic->setTx($tx);
        $this->assertSame($tx, $this->dailyTraffic->getTx());

        // 测试 null 值
        $this->dailyTraffic->setTx('0');
        $this->assertSame('0', $this->dailyTraffic->getTx());
    }

    public function testTxWithLargeValues(): void
    {
        // 测试大数值
        $largeTx = '999999999999999999';
        $this->dailyTraffic->setTx($largeTx);
        $this->assertSame($largeTx, $this->dailyTraffic->getTx());

        // 测试零值
        $this->dailyTraffic->setTx('0');
        $this->assertSame('0', $this->dailyTraffic->getTx());
    }

    public function testRxGetterAndSetter(): void
    {
        $rx = '2048000';

        $this->dailyTraffic->setRx($rx);
        $this->assertSame($rx, $this->dailyTraffic->getRx());

        // 测试 null 值
        $this->dailyTraffic->setRx('0');
        $this->assertSame('0', $this->dailyTraffic->getRx());
    }

    public function testRxWithLargeValues(): void
    {
        // 测试大数值
        $largeRx = '888888888888888888';
        $this->dailyTraffic->setRx($largeRx);
        $this->assertSame($largeRx, $this->dailyTraffic->getRx());

        // 测试零值
        $this->dailyTraffic->setRx('0');
        $this->assertSame('0', $this->dailyTraffic->getRx());
    }

    public function testToStringWithCompleteData(): void
    {
        $node = new Node();
        $this->dailyTraffic->setNode($node);
        $this->dailyTraffic->setIp('192.168.1.100');
        $this->dailyTraffic->setDate(new \DateTime('2023-01-01'));

        $expectedPattern = '/DailyTraffic\[\] 192\.168\.1\.100 - 2023-01-01/';
        $this->assertMatchesRegularExpression($expectedPattern, (string) $this->dailyTraffic);
    }

    public function testToStringWithMissingData(): void
    {
        // 只设置部分数据
        $this->dailyTraffic->setIp('127.0.0.1');
        $this->dailyTraffic->setDate(new \DateTime('2023-12-31'));

        $result = (string) $this->dailyTraffic;
        $this->assertStringContainsString('127.0.0.1', $result);
        $this->assertStringContainsString('2023-12-31', $result);
    }

    public function testCompleteWorkflow(): void
    {
        // 模拟完整的数据设置流程
        $node = new Node();
        $ip = '10.0.0.1';
        $date = new \DateTime('2023-06-15');
        $tx = '5000000';
        $rx = '8000000';

        // 设置所有属性
        $this->dailyTraffic->setNode($node);
        $this->dailyTraffic->setIp($ip);
        $this->dailyTraffic->setDate($date);
        $this->dailyTraffic->setTx($tx);
        $this->dailyTraffic->setRx($rx);

        $this->assertSame($node, $this->dailyTraffic->getNode());
        $this->assertSame($ip, $this->dailyTraffic->getIp());
        $this->assertSame($date, $this->dailyTraffic->getDate());
        $this->assertSame($tx, $this->dailyTraffic->getTx());
        $this->assertSame($rx, $this->dailyTraffic->getRx());
    }
}
