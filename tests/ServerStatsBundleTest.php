<?php

namespace ServerStatsBundle\Tests;

use PHPUnit\Framework\TestCase;
use ServerNodeBundle\ServerNodeBundle;
use ServerStatsBundle\ServerStatsBundle;
use Tourze\BundleDependency\BundleDependencyInterface;

class ServerStatsBundleTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $bundle = new ServerStatsBundle();
        
        $this->assertInstanceOf(BundleDependencyInterface::class, $bundle);
    }
    
    public function testGetBundleDependencies(): void
    {
        $dependencies = ServerStatsBundle::getBundleDependencies();
        
        $this->assertArrayHasKey(ServerNodeBundle::class, $dependencies);
        $this->assertEquals(['all' => true], $dependencies[ServerNodeBundle::class]);
    }
} 