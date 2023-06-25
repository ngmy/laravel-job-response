<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse;

use Williamjulianvicary\LaravelJobResponse\Transport\CacheTransport;
use Williamjulianvicary\LaravelJobResponse\Transport\RedisTransport;
use Williamjulianvicary\LaravelJobResponse\Transport\TransportContract;

class TransportFactory
{
    public const REDIS = 'redis';
    public const CACHE = 'cache';

    public const TRANSPORT_TYPES = [
        self::REDIS,
        self::CACHE,
    ];

    private const CLASS_MAP = [
        self::REDIS => RedisTransport::class,
        self::CACHE => CacheTransport::class,
    ];

    /** @var array{cache?: CacheTransport, redis?: RedisTransport}|array{} */
    private array $instances = [];

    /**
     * @param 'cache'|'redis' $transport
     *
     * @return CacheTransport|RedisTransport
     *
     * @phpstan-return ($transport is 'cache' ? CacheTransport : ($transport is 'redis' ? RedisTransport : never))
     */
    public function getTransport(string $transport = 'redis'): TransportContract
    {
        if (!\in_array($transport, self::TRANSPORT_TYPES, true)) {
            throw new \InvalidArgumentException('Transport unknown.');
        }

        if (!isset($this->instances[$transport])) {
            $class = self::CLASS_MAP[$transport];
            $this->instances[$transport] = new $class();
        }

        return $this->instances[$transport];
    }
}
