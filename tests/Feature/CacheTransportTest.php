<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Feature;

use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Williamjulianvicary\LaravelJobResponse\Exceptions\TimeoutException;
use Williamjulianvicary\LaravelJobResponse\Tests\TestCase;
use Williamjulianvicary\LaravelJobResponse\Transport\CacheTransport;

/**
 * @internal
 *
 * @coversNothing
 */
final class CacheTransportTest extends TestCase
{
    public function testExceptionThrownForIncorrectStoreType(): void
    {
        Config::set('cache.default', 'apc');
        $this->expectException(\InvalidArgumentException::class);
        new CacheTransport();
    }

    public function testExceptionThrownWhenLockCannotBeClaimed(): void
    {
        $this->expectException(TimeoutException::class);
        $lock = Cache::lock('test:lock', 30);

        $lock->get();
        $cacheTransport = new CacheTransport();
        $cacheTransport->lockWaitSeconds = 1;
        $cacheTransport->sendResponse('test', ['response' => 'test']);

        $lock->release();
    }

    protected function getEnvironmentSetUp($app): void
    {
        $config = $app['config'];
        \assert($config instanceof Repository);
        $config->set('job-response.transport', 'cache');
        $config->set('cache.default', 'array');
    }
}
