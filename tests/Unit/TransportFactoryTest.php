<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Unit;

use Williamjulianvicary\LaravelJobResponse\Tests\TestCase;
use Williamjulianvicary\LaravelJobResponse\Transport\CacheTransport;
use Williamjulianvicary\LaravelJobResponse\Transport\RedisTransport;
use Williamjulianvicary\LaravelJobResponse\TransportFactory;

/**
 * @internal
 *
 * @coversNothing
 */
final class TransportFactoryTest extends TestCase
{
    public function testExceptionThrownWhenIncorrectTransportTypeAttempted(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        app(TransportFactory::class)->getTransport('test');
    }

    public function testCacheTransportReturned(): void
    {
        $transport = app(TransportFactory::class)->getTransport('cache');
        self::assertInstanceOf(CacheTransport::class, $transport);
    }

    public function testRedisTransportReturned(): void
    {
        $transport = app(TransportFactory::class)->getTransport('redis');
        self::assertInstanceOf(RedisTransport::class, $transport);
    }
}
