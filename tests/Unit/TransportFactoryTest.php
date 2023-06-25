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
 * @coversNothing
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
