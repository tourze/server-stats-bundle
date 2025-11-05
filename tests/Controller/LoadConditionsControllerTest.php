<?php

namespace ServerStatsBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use ServerStatsBundle\Controller\LoadConditionsController;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;

/**
 * @internal
 */
#[CoversClass(LoadConditionsController::class)]
#[RunTestsInSeparateProcesses]
final class LoadConditionsControllerTest extends AbstractWebTestCase
{
    #[Test]
    public function testPostRequestWithoutAuthenticationReturnsError(): void
    {
        $client = self::createClient();

        $client->request('POST', '/api/load_conditions/', [], [], [], '{}');

        $response = $client->getResponse();

        // 没有认证信息应该返回错误状态码或重定向到登录页
        $this->assertContains($response->getStatusCode(), [302, 400, 401]);
    }

    #[Test]
    public function testPostRequestWithInvalidNodeIdReturnsError(): void
    {
        $client = self::createClient();

        $client->request('POST', '/api/load_conditions/?node_id=999', [], [], [], '{}');

        $response = $client->getResponse();

        // 无效的节点ID应该返回错误状态码或重定向到登录页
        $this->assertContains($response->getStatusCode(), [302, 400, 401, 404]);
    }

    #[DataProvider('provideNotAllowedMethods')]
    #[Test]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClient();
        $client->request($method, '/api/load_conditions/');
        $response = $client->getResponse();
        $this->assertEquals(405, $response->getStatusCode());
    }
}
