<?php

declare(strict_types=1);

namespace ServerStatsBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ServerStatsBundle\ServerStatsBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(ServerStatsBundle::class)]
#[RunTestsInSeparateProcesses]
final class ServerStatsBundleTest extends AbstractBundleTestCase
{
}
