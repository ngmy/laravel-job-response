<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Transport;

use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Williamjulianvicary\LaravelJobResponse\ExceptionResponse;
use Williamjulianvicary\LaravelJobResponse\Exceptions\TimeoutException;
use Williamjulianvicary\LaravelJobResponse\Response;
use Williamjulianvicary\LaravelJobResponse\ResponseCollection;
use Williamjulianvicary\LaravelJobResponse\ResponseContract;

class RedisTransport extends TransportAbstract implements TransportContract
{
    private Connection $connection;

    public function __construct(?string $connection = null)
    {
        \assert(\is_string(Config::get('job-response.redis.connection')) || null === Config::get('job-response.redis.connection'));
        $connection ??= (string) Config::get('job-response.redis.connection');
        $this->connection = Redis::connection($connection);
    }

    public function awaitResponse(string $id, int $timeout): ResponseContract
    {
        $rawBody = $this->connection->blpop($id, $timeout);

        if (null === $rawBody) {
            throw new TimeoutException('Redis response timed out');
        }

        \assert(\is_array($rawBody));
        [$queueId, $response] = $rawBody;
        \assert(\is_string($response));

        return $this->createResponse($this->fromStorage($response));
    }

    public function awaitResponses(string $id, int $expectedResponses, int $timeout): ResponseCollection
    {
        /** @var ResponseCollection<array-key, ExceptionResponse|Response> $responses */
        $responses = new ResponseCollection();
        $timeoutExpiresAt = now()->addSeconds($timeout);
        while (true) {
            if ($responses->count() >= $expectedResponses) {
                break;
            }

            if ($timeoutExpiresAt < now()) {
                throw new TimeoutException('Timed out waiting for response');
            }

            $responses->push($this->awaitResponse($id, $timeout));
        }

        return $responses;
    }

    public function sendResponse(string $id, array $data): void
    {
        $this->connection->rpush($id, $this->forStorage($data));
        $this->connection->expire($id, $this->cacheTtl);
    }

    /**
     * @param TransportResponse $data
     */
    private function forStorage(array $data): string
    {
        return serialize($data);
    }

    /**
     * @return TransportResponse
     */
    private function fromStorage(string $data): array
    {
        // No safety added, this is an internal-only serialization and should not be an attack vector.
        // @noinspection UnserializeExploitsInspection
        /** @var TransportResponse $unserialized */
        $unserialized = unserialize($data);
        \assert(\is_array($unserialized));

        return $unserialized;
    }
}
