<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Unit;

use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestJob;
use Williamjulianvicary\LaravelJobResponse\Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SyncQueueDriverTest extends TestCase
{
    public function testSyncDriverWorksWithSuccess(): void
    {
        $job = new TestJob();
        $response = $job->awaitResponse(2);
        self::assertTrue($response->getData());
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('job-response.transport', 'cache');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('queue.defaultsync');
    }
}
