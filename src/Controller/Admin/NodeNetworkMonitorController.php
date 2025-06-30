<?php

namespace ServerStatsBundle\Controller\Admin;

use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Service\NodeMonitorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NodeNetworkMonitorController extends AbstractController
{
    public function __construct(
        private readonly NodeMonitorService $nodeMonitorService,
    ) {
    }

    #[Route(path: '/admin/node-stats/{id}/network-monitor', name: 'server_stats_node_network_monitor')]
    public function __invoke(Node $node, Request $request): Response
    {
        // 获取网络监控数据
        $monitorData = $this->nodeMonitorService->getNetworkMonitorData($node);

        // 返回视图
        return $this->render('@ServerStats/admin/network_monitor.html.twig', array_merge([
            'node' => $node,
            'referer' => $request->headers->get('referer'),
        ], $monitorData));
    }
}