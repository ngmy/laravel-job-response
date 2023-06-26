<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Feature;

use Illuminate\Config\Repository;
use Williamjulianvicary\LaravelJobResponse\Facades\LaravelJobResponse;
use Williamjulianvicary\LaravelJobResponse\Response;
use Williamjulianvicary\LaravelJobResponse\ResponseCollection;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestJob;
use Williamjulianvicary\LaravelJobResponse\Tests\TestCase;

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
final class FacadeMethodTest extends TestCase
{
    public function testGenerateIdent(): void
    {
        self::assertIsString(LaravelJobResponse::generateIdent());
    }

    public function testTraitAwaitResponse(): void
    {
        $job = new TestJob();
        $response = $job->awaitResponse(10);
        self::assertInstanceOf(Response::class, $response);
    }

    public function testAwaitResponse(): void
    {
        $job = new TestJob();
        $response = LaravelJobResponse::awaitResponse($job, 10);

        self::assertInstanceOf(Response::class, $response);
    }

    public function testAwaitResponses(): void
    {
        $jobs = [new TestJob(), new TestJob()];
        $responses = LaravelJobResponse::awaitResponses($jobs, 10);

        self::assertInstanceOf(ResponseCollection::class, $responses);
        self::assertCount(2, $responses);
    }

    protected function getEnvironmentSetUp($app): void
    {
        $config = $app['config'];
        \assert($config instanceof Repository);
        $config->set('queue.default', 'sync');
        $config->set('cache.default', 'array');
        $config->set('job-response.transport', 'cache');
    }
}
