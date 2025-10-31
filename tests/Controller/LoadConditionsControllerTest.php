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

        // 没有认证信息应该返回错误状态码
        $this->assertContains($response->getStatusCode(), [400, 401]);
    }

    #[Test]
    public function testPostRequestWithInvalidNodeIdReturnsError(): void
    {
        $client = self::createClient();

        $client->request('POST', '/api/load_conditions/?node_id=999', [], [], [], '{}');

        $response = $client->getResponse();

        // 无效的节点ID应该返回错误状态码
        $this->assertContains($response->getStatusCode(), [400, 401, 404]);
    }

    #[Test]
    public function testGetMethodNotAllowed(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/load_conditions/');

        $response = $client->getResponse();

        // GET方法不被支持，应该返回405
        $this->assertEquals(405, $response->getStatusCode());
    }

    #[Test]
    public function testPutMethodNotAllowed(): void
    {
        $client = self::createClient();

        $client->request('PUT', '/api/load_conditions/');

        $response = $client->getResponse();

        // PUT方法不被支持，应该返回405
        $this->assertEquals(405, $response->getStatusCode());
    }

    #[Test]
    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClient();

        $client->request('DELETE', '/api/load_conditions/');

        $response = $client->getResponse();

        // DELETE方法不被支持，应该返回405
        $this->assertEquals(405, $response->getStatusCode());
    }

    #[Test]
    public function testPatchMethodNotAllowed(): void
    {
        $client = self::createClient();

        $client->request('PATCH', '/api/load_conditions/');

        $response = $client->getResponse();

        // PATCH方法不被支持，应该返回405
        $this->assertEquals(405, $response->getStatusCode());
    }

    #[Test]
    public function testOptionsMethodNotAllowed(): void
    {
        $client = self::createClient();

        $client->request('OPTIONS', '/api/load_conditions/');

        $response = $client->getResponse();

        // OPTIONS方法不被支持，应该返回405
        $this->assertEquals(405, $response->getStatusCode());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClient();
        $client->request($method, '/api/load_conditions/');
        $response = $client->getResponse();
        $this->assertEquals(405, $response->getStatusCode());
    }
}
