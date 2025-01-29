<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Unit;

use Illuminate\Support\Facades\App;
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
final class TransportFactoryTest extends TestCase
{
    public function testExceptionThrownWhenIncorrectTransportTypeAttempted(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        // @phpstan-ignore-next-line
        App::make(TransportFactory::class)->getTransport('test');
    }

    public function testCacheTransportReturned(): void
    {
        $transport = App::make(TransportFactory::class)->getTransport('cache');
        self::assertInstanceOf(CacheTransport::class, $transport);
    }

    public function testRedisTransportReturned(): void
    {
        $transport = App::make(TransportFactory::class)->getTransport('redis');
        self::assertInstanceOf(RedisTransport::class, $transport);
    }
}
