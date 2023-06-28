<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Transport;

use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Williamjulianvicary\LaravelJobResponse\Exceptions\TimeoutException;
use Williamjulianvicary\LaravelJobResponse\ResponseCollection;
use Williamjulianvicary\LaravelJobResponse\ResponseContract;

class CacheTransport extends TransportAbstract implements TransportContract
{
    /**
     * How frequently should we poll the cache for the response?
     */
    public int $millisecondPollWait = 250;

    /**
     * How long should we wait to acquire a lock, at most?
     */
    public int $lockWaitSeconds = 5;

    /**
     * How long should the lock be held for?
     */
    public int $lockHoldSeconds = 5;

    /**
     * The suffix used for the lock key.
     */
    private string $lockIdSuffix = ':lock';

    /**
     * Instance of the cache store used for all storage/collection calls.
     */
    private readonly Repository $cacheStore;

    public function __construct(string $store = null)
    {
        \assert(\is_string(Config::get('job-transport.cache.store')) || null === Config::get('job-transport.cache.store'));
        $store ??= (string) Config::get('job-transport.cache.store');
        $this->cacheStore = Cache::store($store);

        if (!method_exists($this->cacheStore->getStore(), 'lock')) {
            throw new \InvalidArgumentException(
                'The cache driver provided does not support locking. Try Array, Database, Memcached, Redis.'
            );
        }
    }

    public function awaitResponse(string $id, int $timeout): ResponseContract
    {
        $responses = $this->_awaitResponses($id, $timeout, 1);
        $response = $responses[0];

        return $this->createResponse($response);
    }

    public function awaitResponses(string $id, int $expectedResponses, int $timeout): ResponseCollection
    {
        return $this->createResponses($this->_awaitResponses($id, $timeout, $expectedResponses));
    }

    public function sendResponse(string $id, array $data): void
    {
        $store = $this->cacheStore->getStore();
        \assert($store instanceof LockProvider);
        $lock = $store->lock($id.$this->lockIdSuffix, $this->lockHoldSeconds);

        try {
            $lock->block($this->lockWaitSeconds);

            $cacheData = $this->cacheStore->get($id, []);
            \assert(\is_array($cacheData));
            $cacheData[] = $data;
            $this->cacheStore->put($id, $cacheData, $this->cacheTtl);
        } catch (LockTimeoutException $e) {
            throw new TimeoutException('Timed out attempting to acquire cache lock - something went wrong.');
        } finally {
            $lock->release();
        }
    }

    /**
     * @return list<array{response?: mixed, exception?: array{
     *     exception_class: string,
     *     exception_basename: string,
     *     message: string,
     *     file: string,
     *     code: int,
     *     trace: string,
     *     line: int,
     * }|array{}}>
     *
     * @throws TimeoutException
     */
    private function _awaitResponses(string $id, int $timeout, int $expectedResponses = 1): array
    {
        $timeoutAt = now()->addSeconds($timeout);

        $responses = [];
        while (true) {
            if ($timeoutAt < now()) {
                throw new TimeoutException('Timed out while waiting for a response');
            }

            if ($responses = $this->cacheStore->get($id)) {
                \assert(\is_array($responses));
                if (\count($responses) >= $expectedResponses) {
                    break;
                }
            }

            usleep($this->millisecondPollWait * 1000);
        }

        \assert(\is_array($responses));

        return $responses;
    }
}
