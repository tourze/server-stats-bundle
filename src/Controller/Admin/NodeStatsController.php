<?php

namespace ServerStatsBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use ServerNodeBundle\Entity\Node;
use ServerStatsBundle\Service\NodeMonitorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/node-stats')]
class NodeStatsController extends AbstractController
{
    public function __construct(
        private readonly NodeMonitorService $nodeMonitorService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    /**
     * 网络监控
     */
    #[Route('/{id}/network-monitor', name: 'server_stats_node_network_monitor')]
    public function networkMonitor(Node $node, Request $request): Response
    {
        // 获取网络监控数据
        $monitorData = $this->nodeMonitorService->getNetworkMonitorData($node);

        // 返回视图
        return $this->render('@ServerStats/admin/network_monitor.html.twig', array_merge([
            'node' => $node,
            'referer' => $request->headers->get('referer'),
        ], $monitorData));
    }

    /**
     * 负载监控
     */
    #[Route('/{id}/load-monitor', name: 'server_stats_node_load_monitor')]
    public function loadMonitor(Node $node, Request $request): Response
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
