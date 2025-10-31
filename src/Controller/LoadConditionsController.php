<?php

namespace ServerStatsBundle\Controller;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use ServerNodeBundle\Entity\Node;
use ServerNodeBundle\Enum\NodeStatus;
use ServerNodeBundle\Repository\NodeRepository;
use ServerStatsBundle\Entity\DailyTraffic;
use ServerStatsBundle\Entity\MinuteStat;
use ServerStatsBundle\Entity\MonthlyTraffic;
use ServerStatsBundle\Repository\DailyTrafficRepository;
use ServerStatsBundle\Repository\MonthlyTrafficRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Json\Json;

#[WithMonologChannel(channel: 'server_stats')]
final class LoadConditionsController extends AbstractController
{
    public function __construct(
        #[Autowire(service: 'cache.app')] private readonly AdapterInterface $cache,
        private readonly NodeRepository $nodeRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly DailyTrafficRepository $dailyTrafficRepository,
        private readonly MonthlyTrafficRepository $monthlyTrafficRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route(path: '/api/load_conditions/', methods: 'POST')]
    public function __invoke(Request $request): Response
    {
        $this->logger->info('收到HTTP请求', [
            'uri' => $request->getUri(),
            'headers' => $request->headers->all(),
            'body' => $request->getContent(),
        ]);

        $now = CarbonImmutable::now();
        $node = $this->getNodeFromRequest($request);
        if (null === $node) {
            throw new BadRequestException('Invalid node identification');
        }

        $this->updateNodeCache($node);
        $nodeLoadConditionsDic = Json::decode($request->getContent());
        $this->updateNodeIpIfChanged($node, $request);

        if (NodeStatus::MAINTAIN === $node->getStatus()) {
            $this->logger->warning('维护中的节点先不存信息, 避免流量重复存储', [
                'nodeId' => $node->getId(),
            ]);

            return new Response('success');
        }

        $this->saveTrafficData($node, $nodeLoadConditionsDic, $now, $request);
        $this->saveMinuteStatIfNeeded($node, $nodeLoadConditionsDic, $now);
        $this->updateNodeStatusIfNeeded($node);

        return $this->json(['msg' => 'success']);
    }

    private function updateNodeCache(Node $node): void
    {
        $cacheItem = $this->cache->getItem('REDIS_ONLINE_NODE_' . $node->getId());
        $cacheItem->set(1);
        $cacheItem->expiresAfter(480);
        $this->cache->save($cacheItem);
    }

    private function updateNodeIpIfChanged(Node $node, Request $request): void
    {
        $nodeIp = $request->getClientIp();
        if ($nodeIp !== $node->getOnlineIp()) {
            $node->setOnlineIp($nodeIp);
            $this->entityManager->persist($node);
            $this->entityManager->flush();

            $this->logger->info('节点IP变更', [
                'nodeId' => $node->getId(),
                'oldIp' => $node->getOnlineIp(),
                'newIp' => $nodeIp,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $nodeLoadConditionsDic
     */
    private function saveTrafficData(Node $node, array $nodeLoadConditionsDic, CarbonImmutable $now, Request $request): void
    {
        $nodeIp = $request->getClientIp() ?? '0.0.0.0';
        $today = $now->startOfDay();

        $this->saveDailyTraffic($node, $nodeLoadConditionsDic, $today, $nodeIp);
        $this->saveMonthlyTraffic($node, $nodeLoadConditionsDic, $now, $nodeIp);
    }

    /**
     * @param array<string, mixed> $nodeLoadConditionsDic
     */
    private function saveDailyTraffic(Node $node, array $nodeLoadConditionsDic, CarbonImmutable $today, string $nodeIp): void
    {
        $todayTx = $nodeLoadConditionsDic['todayTx'];
        $todayRx = $nodeLoadConditionsDic['todayRx'];

        $nodeTrafficDay = $this->dailyTrafficRepository->findOneBy([
            'node' => $node,
            'date' => $today,
        ]);

        if (null === $nodeTrafficDay) {
            $nodeTrafficDay = new DailyTraffic();
            $nodeTrafficDay->setNode($node);
            $nodeTrafficDay->setDate($today);
            $nodeTrafficDay->setIp($nodeIp);
            $nodeTrafficDay->setRx('0');
            $nodeTrafficDay->setTx('0');
        }

        if ($nodeTrafficDay->getRx() < $todayRx) {
            $nodeTrafficDay->setRx($todayRx);
        }
        if ($nodeTrafficDay->getTx() < $todayTx) {
            $nodeTrafficDay->setTx($todayTx);
        }

        $this->entityManager->persist($nodeTrafficDay);
        $this->entityManager->flush();
    }

    /**
     * @param array<string, mixed> $nodeLoadConditionsDic
     */
    private function saveMonthlyTraffic(Node $node, array $nodeLoadConditionsDic, CarbonImmutable $now, string $nodeIp): void
    {
        $monthRx = $nodeLoadConditionsDic['monthRx'];
        $monthTx = $nodeLoadConditionsDic['monthTx'];

        $nodeTrafficMonth = $this->monthlyTrafficRepository->findOneBy([
            'node' => $node,
            'month' => $now->format('Y-m'),
        ]);

        if (null === $nodeTrafficMonth) {
            $nodeTrafficMonth = new MonthlyTraffic();
            $nodeTrafficMonth->setNode($node);
            $nodeTrafficMonth->setMonth($now->format('Y-m'));
            $nodeTrafficMonth->setIp($nodeIp);
            $nodeTrafficMonth->setRx('0');
            $nodeTrafficMonth->setTx('0');
        }

        if ($nodeTrafficMonth->getRx() < $monthRx) {
            $nodeTrafficMonth->setRx($monthRx);
        }
        if ($nodeTrafficMonth->getTx() < $monthTx) {
            $nodeTrafficMonth->setTx($monthTx);
        }

        $this->entityManager->persist($nodeTrafficMonth);
        $this->entityManager->flush();
    }

    /**
     * @param array<string, mixed> $nodeLoadConditionsDic
     */
    private function saveMinuteStatIfNeeded(Node $node, array $nodeLoadConditionsDic, CarbonImmutable $now): void
    {
        $fiveTx = ArrayHelper::getValue($nodeLoadConditionsDic, 'fiveTx', 0);
        $fiveRx = ArrayHelper::getValue($nodeLoadConditionsDic, 'fiveRx', 0);

        $lastTxItem = $this->cache->getItem('LAST_FIVE_NODE_TX_' . $node->getId());
        $lastTx = $lastTxItem->isHit() ? $lastTxItem->get() : 0;
        $lastRxItem = $this->cache->getItem('LAST_FIVE_NODE_RX_' . $node->getId());
        $lastRx = $lastRxItem->isHit() ? $lastRxItem->get() : 0;

        if ($lastTx !== $fiveTx || $lastRx !== $fiveRx) {
            $this->updateTrafficCache($node, $fiveTx, $fiveRx);
            $this->createMinuteStat($node, $nodeLoadConditionsDic, $now, $fiveTx, $fiveRx);
        }
    }

    private function updateTrafficCache(Node $node, int $fiveTx, int $fiveRx): void
    {
        $txCacheItem = $this->cache->getItem('LAST_FIVE_NODE_TX_' . $node->getId());
        $txCacheItem->set($fiveTx);
        $txCacheItem->expiresAfter(60 * 60 * 24);
        $this->cache->save($txCacheItem);

        $rxCacheItem = $this->cache->getItem('LAST_FIVE_NODE_RX_' . $node->getId());
        $rxCacheItem->set($fiveRx);
        $rxCacheItem->expiresAfter(60 * 60 * 24);
        $this->cache->save($rxCacheItem);
    }

    /**
     * @param array<string, mixed> $nodeLoadConditionsDic
     */
    private function createMinuteStat(Node $node, array $nodeLoadConditionsDic, CarbonImmutable $now, int $fiveTx, int $fiveRx): void
    {
        $totalRam = $nodeLoadConditionsDic['totalRam'];
        $ramUsed = $nodeLoadConditionsDic['ramUsed'];
        $loadConditions = $nodeLoadConditionsDic['loadConditions'];

        $bytesSent2min = $fiveTx / 300;
        $bytesRecv2min = $fiveRx / 300;

        $minuteStat = new MinuteStat();
        $minuteStat->setNode($node);
        $minuteStat->setDatetime($now);

        $loadData = $this->parseLoadConditions($loadConditions);
        if (null !== $loadData) {
            $minuteStat->setLoadOneMinute((string) $loadData['1min']);
            $minuteStat->setLoadFiveMinutes((string) $loadData['5min']);
            $minuteStat->setLoadFifteenMinutes((string) $loadData['15min']);
        }

        $minuteStat->setMemoryTotal($totalRam);
        $minuteStat->setMemoryUsed($ramUsed);
        $minuteStat->setMemoryFree($totalRam - $ramUsed);
        $minuteStat->setRxBandwidth((string) $bytesRecv2min);
        $minuteStat->setTxBandwidth((string) $bytesSent2min);

        $this->entityManager->persist($minuteStat);
        $this->entityManager->flush();
    }

    /**
     * @return array<string, float>|null
     */
    private function parseLoadConditions(mixed $loadConditions): ?array
    {
        if (!is_string($loadConditions) || '' === trim($loadConditions)) {
            return null;
        }

        $parts = explode(' ', trim($loadConditions));
        if (count($parts) < 3) {
            return null;
        }

        return [
            '1min' => (float) $parts[0],
            '5min' => (float) $parts[1],
            '15min' => (float) $parts[2],
        ];
    }

    private function updateNodeStatusIfNeeded(Node $node): void
    {
        if (in_array($node->getStatus(), [NodeStatus::INIT, NodeStatus::OFFLINE], true)) {
            $node->setStatus(NodeStatus::ONLINE);
            $this->entityManager->persist($node);
            $this->entityManager->flush();
        }
    }

    /**
     * 从请求中获取节点
     * 支持多种认证方式
     */
    private function getNodeFromRequest(Request $request): ?Node
    {
        // 方式1: 通过query参数node_id
        if ($request->query->has('node_id')) {
            $node = $this->nodeRepository->find($request->query->get('node_id'));

            return $node instanceof Node ? $node : null;
        }

        // 方式2: 通过Authorization头
        $authorization = $request->headers->get('authorization');
        if (null === $authorization) {
            return null;
        }

        $parts = explode('|', $authorization);
        if (4 !== count($parts)) {
            return null;
        }

        [$apiKey, $nonceStr, $signature, $timestamp] = $parts;

        // 时间戳校验 (允许10分钟误差)
        if (abs(time() - (int) $timestamp) > 600) {
            return null;
        }

        // 根据API_KEY查找对应的节点
        $node = $this->nodeRepository->findOneBy(['apiKey' => $apiKey]);
        if (null === $node || !$node instanceof Node) {
            return null;
        }

        // 校验签名是否正确
        $apiSecret = $node->getApiSecret();
        if (null === $apiSecret) {
            return null;
        }

        $signStr = "{$apiKey}|{$nonceStr}|{$apiSecret}|{$timestamp}";
        if (md5($signStr) !== $signature) {
            return null;
        }

        return $node;
    }
}
