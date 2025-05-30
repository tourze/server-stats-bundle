<?php

namespace ServerStatsBundle\Controller;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
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

class LoadConditionsController extends AbstractController
{
    public function __construct(
        #[Autowire(service: 'cache.app')] private readonly AdapterInterface $cache,
        private readonly NodeRepository $nodeRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly DailyTrafficRepository $dailyTrafficRepository,
        private readonly MonthlyTrafficRepository $monthlyTrafficRepository,
        private readonly LoggerInterface $logger,
    )
    {
    }

    #[Route(path: '/api/load_conditions/', methods: 'POST')]
    public function loadConditions(Request $request): Response
    {
        $this->logger->info('收到HTTP请求', [
            'uri' => $request->getUri(),
            'headers' => $request->headers->all(),
            'body' => $request->getContent(),
        ]);

        $now = Carbon::now();
        $today = $now->startOfDay();

        $node = $this->getNodeFromRequest($request);
        if (!$node) {
            throw new BadRequestException('Invalid node identification');
        }

        $cacheItem = $this->cache->getItem('REDIS_ONLINE_NODE_' . $node->getId());
        $cacheItem->set(1);
        $cacheItem->expiresAfter(480);
        $this->cache->save($cacheItem);

        $nodeLoadConditionsDic = Json::decode($request->getContent());
        $cpuUsedPercentage = $nodeLoadConditionsDic['cpuUsedPercentage'];
        $totalRam = $nodeLoadConditionsDic['totalRam'];
        $ramUsed = $nodeLoadConditionsDic['ramUsed'];
        $loadConditions = $nodeLoadConditionsDic['loadConditions'];

        // 获取并检查IP变更
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

        if (NodeStatus::MAINTAIN === $node->getStatus()) {
            $this->logger->warning('维护中的节点先不存信息, 避免流量重复存储', [
                'nodeId' => $node->getId(),
            ]);
            return new Response('success');
        }

        $todayTx = $nodeLoadConditionsDic['todayTx']; // 流量单位 byte
        $todayRx = $nodeLoadConditionsDic['todayRx']; // 流量单位 byte
        $monthRx = $nodeLoadConditionsDic['monthRx']; // 流量单位 byte
        $monthTx = $nodeLoadConditionsDic['monthTx']; // 流量单位 byte
        $avgRate = $nodeLoadConditionsDic['avgRate']; // 五分钟平均速率 KBytes/S

        $fiveTx = ArrayHelper::getValue($nodeLoadConditionsDic, 'fiveTx', 0); // 五分钟 Tx 速率 byte
        $fiveRx = ArrayHelper::getValue($nodeLoadConditionsDic, 'fiveRx', 0); // 五分钟 Rx 速率 byte

        // 日流量入库
        $nodeTrafficDay = $this->dailyTrafficRepository->findOneBy([
            'node' => $node,
            'date' => $today,
        ]);
        if (!$nodeTrafficDay) {
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

        // 月流量入库
        $nodeTrafficMonth = $this->monthlyTrafficRepository->findOneBy([
            'node' => $node,
            'month' => $now->format('Y-m'),
        ]);
        if (!$nodeTrafficMonth) {
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

        // 五分钟平均速率获取
        $lastTxItem = $this->cache->getItem('LAST_FIVE_NODE_TX_' . $node->getId());
        $lastTx = $lastTxItem->isHit() ? $lastTxItem->get() : 0;
        $lastRxItem = $this->cache->getItem('LAST_FIVE_NODE_RX_' . $node->getId());
        $lastRx = $lastRxItem->isHit() ? $lastRxItem->get() : 0;
        if ($lastTx != $fiveTx || $lastRx != $fiveRx) {
            // 因为是 5 分钟才变化一次 这里只保存 5分钟的值
            $txCacheItem = $this->cache->getItem('LAST_FIVE_NODE_TX_' . $node->getId());
            $txCacheItem->set($fiveTx);
            $txCacheItem->expiresAfter(60 * 60 * 24);
            $this->cache->save($txCacheItem);

            $rxCacheItem = $this->cache->getItem('LAST_FIVE_NODE_RX_' . $node->getId());
            $rxCacheItem->set($fiveRx);
            $rxCacheItem->expiresAfter(60 * 60 * 24);
            $this->cache->save($rxCacheItem);

            $bytesSent2min = $fiveTx / 300; // bytes
            $bytesRecv2min = $fiveRx / 300; // bytes

            // 记录到MinuteStat
            $minuteStat = new MinuteStat();
            $minuteStat->setNode($node);
            $minuteStat->setDatetime($now);

            // 解析 loadConditions 字符串数据 (来自 /proc/loadavg)
            // 格式: "0.06 0.04 0.05 1/776 17\n"
            $loadData = null;
            if (is_string($loadConditions) && trim($loadConditions)) {
                $parts = explode(' ', trim($loadConditions));
                if (count($parts) >= 3) {
                    $loadData = [
                        '1min' => (float)$parts[0],
                        '5min' => (float)$parts[1],
                        '15min' => (float)$parts[2],
                    ];
                }
            }

            // 设置负载数据
            if ($loadData) {
                $minuteStat->setLoadOneMinute($loadData['1min']);
                $minuteStat->setLoadFiveMinutes($loadData['5min']);
                $minuteStat->setLoadFifteenMinutes($loadData['15min']);
            }

            // 设置内存数据
            $minuteStat->setMemoryTotal($totalRam);
            $minuteStat->setMemoryUsed($ramUsed);
            $minuteStat->setMemoryFree($totalRam - $ramUsed);

            // 设置网络数据
            $minuteStat->setRxBandwidth($bytesRecv2min);
            $minuteStat->setTxBandwidth($bytesSent2min);

            $this->entityManager->persist($minuteStat);
            $this->entityManager->flush();

            // 统计节点使用流量和在线用户
            // Note: Node实体暂无usedTraffic字段，此处记录流量统计但不更新节点
            $nodeUsedTraffic = $fiveTx; // 计算2分钟之间的流量差. 用于写入数据库
        }

        // 离线或初始化的节点 立刻上线
        if (in_array($node->getStatus(), [NodeStatus::INIT, NodeStatus::OFFLINE])) {
            $node->setStatus(NodeStatus::ONLINE);
            $this->entityManager->persist($node);
            $this->entityManager->flush();
        }

        return new Response('success');
    }

    /**
     * 从请求中获取节点
     * 支持多种认证方式
     */
    private function getNodeFromRequest(Request $request): ?Node
    {
        // 方式1: 通过query参数node_id
        if ($request->query->has('node_id')) {
            return $this->nodeRepository->find($request->query->get('node_id'));
        }

        // 方式2: 通过Authorization头
        $authorization = $request->headers->get('authorization');
        if (!$authorization) {
            return null;
        }

        $parts = explode('|', $authorization);
        if (count($parts) !== 4) {
            return null;
        }

        [$apiKey, $nonceStr, $signature, $timestamp] = $parts;

        // 时间戳校验 (允许10分钟误差)
        if (abs(time() - (int)$timestamp) > 600) {
            return null;
        }

        // 根据API_KEY查找对应的节点
        $node = $this->nodeRepository->findOneBy(['apiKey' => $apiKey]);
        if (!$node) {
            return null;
        }

        // 校验签名是否正确
        $apiSecret = $node->getApiSecret();
        if (!$apiSecret) {
            return null;
        }

        $signStr = "{$apiKey}|{$nonceStr}|{$apiSecret}|{$timestamp}";
        if (md5($signStr) !== $signature) {
            return null;
        }

        return $node;
    }
}
