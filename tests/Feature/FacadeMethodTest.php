<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Feature;

use Illuminate\Config\Repository;
use PHPUnit\Framework\Attributes\CoversClass;
use Williamjulianvicary\LaravelJobResponse\CanRespond;
use Williamjulianvicary\LaravelJobResponse\ExceptionResponse;
use Williamjulianvicary\LaravelJobResponse\Exceptions\JobFailedException;
use Williamjulianvicary\LaravelJobResponse\Exceptions\TimeoutException;
use Williamjulianvicary\LaravelJobResponse\Facades\LaravelJobResponse;
use Williamjulianvicary\LaravelJobResponse\LaravelJobResponseServiceProvider;
use Williamjulianvicary\LaravelJobResponse\Response;
use Williamjulianvicary\LaravelJobResponse\ResponseCollection;
use Williamjulianvicary\LaravelJobResponse\ResponseFactory;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestJob;
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
#[CoversClass(LaravelJobResponse::class)]
#[CoversClass(\Williamjulianvicary\LaravelJobResponse\LaravelJobResponse::class)]
#[CoversClass(LaravelJobResponseServiceProvider::class)]
#[CoversClass(Response::class)]
#[CoversClass(ResponseCollection::class)]
#[CoversClass(ResponseFactory::class)]
#[CoversClass(CacheTransport::class)]
#[CoversClass(RedisTransport::class)]
#[CoversClass(TransportAbstract::class)]
#[CoversClass(TransportFactory::class)]
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
