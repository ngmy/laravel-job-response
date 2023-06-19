<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Feature;

use Williamjulianvicary\LaravelJobResponse\Facades\LaravelJobResponse;
use Williamjulianvicary\LaravelJobResponse\Response;
use Williamjulianvicary\LaravelJobResponse\ResponseCollection;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestJob;
use Williamjulianvicary\LaravelJobResponse\Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
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
        $response = LaravelJobResponse::awaitResponses($jobs, 10);

        self::assertInstanceOf(ResponseCollection::class, $response);
        self::assertCount(2, $response);
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('queue.default', 'sync');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('job-response.transport', 'cache');
    }
}
