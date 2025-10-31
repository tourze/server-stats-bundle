<?php

namespace ServerStatsBundle\Controller\Admin;

use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Service\NodeMonitorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NodeLoadMonitorController extends AbstractController
{
    public function __construct(
        private readonly NodeMonitorService $nodeMonitorService,
    ) {
    }

    #[Route(path: '/admin/node-stats/{id}/load-monitor', name: 'server_stats_node_load_monitor')]
    public function __invoke(Node $node, Request $request): Response
    {
        // 获取负载监控数据
        $monitorData = $this->nodeMonitorService->getLoadMonitorData($node);

        // 返回视图
        return $this->render('@ServerStats/admin/load_monitor.html.twig', array_merge([
            'node' => $node,
            'referer' => $request->headers->get('referer'),
        ], $monitorData));
    }
}
