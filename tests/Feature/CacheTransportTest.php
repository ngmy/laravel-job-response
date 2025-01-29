<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Feature;

use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use Williamjulianvicary\LaravelJobResponse\CanRespond;
use Williamjulianvicary\LaravelJobResponse\ExceptionResponse;
use Williamjulianvicary\LaravelJobResponse\Exceptions\JobFailedException;
use Williamjulianvicary\LaravelJobResponse\Exceptions\TimeoutException;
use Williamjulianvicary\LaravelJobResponse\LaravelJobResponse;
use Williamjulianvicary\LaravelJobResponse\LaravelJobResponseServiceProvider;
use Williamjulianvicary\LaravelJobResponse\Response;
use Williamjulianvicary\LaravelJobResponse\ResponseCollection;
use Williamjulianvicary\LaravelJobResponse\ResponseFactory;
use Williamjulianvicary\LaravelJobResponse\Tests\TestCase;
use Williamjulianvicary\LaravelJobResponse\Transport\CacheTransport;
use Williamjulianvicary\LaravelJobResponse\Transport\RedisTransport;
use Williamjulianvicary\LaravelJobResponse\Transport\TransportAbstract;
use Williamjulianvicary\LaravelJobResponse\TransportFactory;

/**
 * @internal
 */
#[CoversClass(CanRespond::class)]
#[CoversClass(ExceptionResponse::class)]
#[CoversClass(JobFailedException::class)]
#[CoversClass(TimeoutException::class)]
#[CoversClass(\Williamjulianvicary\LaravelJobResponse\Facades\LaravelJobResponse::class)]
#[CoversClass(LaravelJobResponse::class)]
#[CoversClass(LaravelJobResponseServiceProvider::class)]
#[CoversClass(Response::class)]
#[CoversClass(ResponseCollection::class)]
#[CoversClass(ResponseFactory::class)]
#[CoversClass(CacheTransport::class)]
#[CoversClass(RedisTransport::class)]
#[CoversClass(TransportAbstract::class)]
#[CoversClass(TransportFactory::class)]
final class CacheTransportTest extends TestCase
{
    public function testExceptionThrownForIncorrectStoreType(): void
    {
        Config::set('job-response.cache.store', 'apc');
        Config::set('cache.stores.apc', ['driver' => 'apc']);
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
