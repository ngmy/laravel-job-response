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
 * @covers \Williamjulianvicary\LaravelJobResponse\CanRespond
 * @covers \Williamjulianvicary\LaravelJobResponse\ExceptionResponse
 * @covers \Williamjulianvicary\LaravelJobResponse\Exceptions\JobFailedException
 * @covers \Williamjulianvicary\LaravelJobResponse\Exceptions\TimeoutException
 * @covers \Williamjulianvicary\LaravelJobResponse\Facades\LaravelJobResponse
 * @covers \Williamjulianvicary\LaravelJobResponse\LaravelJobResponse
 * @covers \Williamjulianvicary\LaravelJobResponse\LaravelJobResponseServiceProvider
 * @covers \Williamjulianvicary\LaravelJobResponse\Response
 * @covers \Williamjulianvicary\LaravelJobResponse\ResponseCollection
 * @covers \Williamjulianvicary\LaravelJobResponse\ResponseFactory
 * @covers \Williamjulianvicary\LaravelJobResponse\Transport\CacheTransport
 * @covers \Williamjulianvicary\LaravelJobResponse\Transport\RedisTransport
 * @covers \Williamjulianvicary\LaravelJobResponse\Transport\TransportAbstract
 * @covers \Williamjulianvicary\LaravelJobResponse\TransportFactory
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
