<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Unit;

use Illuminate\Support\Facades\App;
use Williamjulianvicary\LaravelJobResponse\Tests\TestCase;
use Williamjulianvicary\LaravelJobResponse\Transport\CacheTransport;
use Williamjulianvicary\LaravelJobResponse\Transport\RedisTransport;
use Williamjulianvicary\LaravelJobResponse\TransportFactory;

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
