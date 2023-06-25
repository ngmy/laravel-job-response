<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Unit;

use Illuminate\Config\Repository;
use Williamjulianvicary\LaravelJobResponse\Response;
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
        \assert($response instanceof $response);
        self::assertInstanceOf(Response::class, $response);
        self::assertTrue($response->getData());
    }

    protected function getEnvironmentSetUp($app): void
    {
        $config = $app['config'];
        \assert($config instanceof Repository);
        $config->set('job-response.transport', 'cache');
        $config->set('cache.default', 'array');
        $config->set('queue.defaultsync');
    }
}
