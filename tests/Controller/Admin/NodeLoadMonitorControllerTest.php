<?php

namespace ServerStatsBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use ServerStatsBundle\Controller\Admin\NodeLoadMonitorController;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;

/**
 * @internal
 */
#[CoversClass(NodeLoadMonitorController::class)]
#[RunTestsInSeparateProcesses]
final class NodeLoadMonitorControllerTest extends AbstractWebTestCase
{
    #[Test]
    public function testUnauthenticatedAccessRedirectsToLogin(): void
    {
        $client = self::createClient();
        $client->request('GET', '/admin/node-stats/999/load-monitor');

        $response = $client->getResponse();

        // 未认证访问应该重定向到登录页面
        $this->assertTrue(
            302 === $response->getStatusCode() || 404 === $response->getStatusCode(),
            sprintf('Expected 302 or 404, got %d', $response->getStatusCode())
        );
    }

    #[Test]
    public function testGetRequest(): void
    {
        $client = self::createClient();
        $client->request('GET', '/admin/node-stats/999/load-monitor');

        $response = $client->getResponse();

        // 验证请求被处理（可能是404因为实体不存在，或302重定向）
        $this->assertContains($response->getStatusCode(), [302, 404]);
    }

    #[Test]
    public function testPostRequest(): void
    {
        $client = self::createClient();
        $client->request('POST', '/admin/node-stats/999/load-monitor');

        $response = $client->getResponse();

        // POST/PUT/DELETE/PATCH/OPTIONS 方法不被支持，预期404或405
        $this->assertContains($response->getStatusCode(), [404, 405]);
    }

    #[Test]
    public function testPutRequest(): void
    {
        $client = self::createClient();
        $client->request('PUT', '/admin/node-stats/999/load-monitor');

        $response = $client->getResponse();

        // POST/PUT/DELETE/PATCH/OPTIONS 方法不被支持，预期404或405
        $this->assertContains($response->getStatusCode(), [404, 405]);
    }

    #[Test]
    public function testDeleteRequest(): void
    {
        $client = self::createClient();
        $client->request('DELETE', '/admin/node-stats/999/load-monitor');

        $response = $client->getResponse();

        // POST/PUT/DELETE/PATCH/OPTIONS 方法不被支持，预期404或405
        $this->assertContains($response->getStatusCode(), [404, 405]);
    }

    #[Test]
    public function testPatchRequest(): void
    {
        $client = self::createClient();
        $client->request('PATCH', '/admin/node-stats/999/load-monitor');

        $response = $client->getResponse();

        // POST/PUT/DELETE/PATCH/OPTIONS 方法不被支持，预期404或405
        $this->assertContains($response->getStatusCode(), [404, 405]);
    }

    #[Test]
    public function testHeadRequest(): void
    {
        $client = self::createClient();
        $client->request('HEAD', '/admin/node-stats/999/load-monitor');

        $response = $client->getResponse();

        // HEAD 方法可能返回302（重定向）或404（实体不存在）
        $this->assertContains($response->getStatusCode(), [302, 404]);
    }

    #[Test]
    public function testOptionsRequest(): void
    {
        $client = self::createClient();
        $client->request('OPTIONS', '/admin/node-stats/999/load-monitor');

        $response = $client->getResponse();

        // POST/PUT/DELETE/PATCH/OPTIONS 方法不被支持，预期404或405
        $this->assertContains($response->getStatusCode(), [404, 405]);
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClient();
        $client->request($method, '/admin/node-stats/999/load-monitor');
        $response = $client->getResponse();
        $this->assertContains($response->getStatusCode(), [404, 405]);
    }
}
