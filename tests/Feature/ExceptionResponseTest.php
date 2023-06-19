<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Feature;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Artisan;
use Williamjulianvicary\LaravelJobResponse\ExceptionResponse;
use Williamjulianvicary\LaravelJobResponse\Exceptions\JobFailedException;
use Williamjulianvicary\LaravelJobResponse\Facades\LaravelJobResponse;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestException;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestExceptionJob;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestManuallyFailedJob;
use Williamjulianvicary\LaravelJobResponse\Tests\TestCase;
use Williamjulianvicary\LaravelJobResponse\Transport\TransportContract;

/**
 * @internal
 *
 * @coversNothing
 */
final class ExceptionResponseTest extends TestCase
{
    /**
     * @group failing
     */
    public function testThrowsJobFailedException(): void
    {
        $this->expectException(JobFailedException::class);
        $job = new TestExceptionJob();
        $job->prepareResponse();

        app(Dispatcher::class)->dispatch($job);

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        $response = app(TransportContract::class)->throwExceptionOnFailure(true)->awaitResponse($job->getResponseIdent(), 1);
    }

    public function testManuallyFailedJob(): void
    {
        $job = new TestManuallyFailedJob();
        $job->prepareResponse();

        app(Dispatcher::class)->dispatch($job);

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        /** @var ExceptionResponse $response */
        $response = app(TransportContract::class)->throwExceptionOnFailure(false)->awaitResponse($job->getResponseIdent(), 1);

        self::assertInstanceOf(ExceptionResponse::class, $response);
        self::assertNull($response->getMessage());
    }

    public function testThrowsJobFailedExceptionGetter(): void
    {
        try {
            $job = new TestExceptionJob();
            $job->prepareResponse();

            app(Dispatcher::class)->dispatch($job);

            Artisan::call('queue:work', [
                '--once' => 1,
            ]);

            $response = app(TransportContract::class)->throwExceptionOnFailure(true)->awaitResponse($job->getResponseIdent(), 1);
        } catch (JobFailedException $e) {
            $exceptionResponse = $e->getExceptionResponse();
        }

        self::assertInstanceOf(ExceptionResponse::class, $exceptionResponse);
    }

    /**
     * @group failing
     */
    public function testDoesNotThrowJobFailedException(): void
    {
        $job = new TestExceptionJob();
        $job->prepareResponse();

        app(Dispatcher::class)->dispatch($job);

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        /** @var ExceptionResponse $response */
        $response = app(TransportContract::class)->throwExceptionOnFailure(false)->awaitResponse($job->getResponseIdent(), 1);
        self::assertInstanceOf(ExceptionResponse::class, $response);
        self::assertSame('TestException', $response->getExceptionBaseName());
        self::assertSame(TestException::class, $response->getExceptionClass());
    }

    /**
     * @group new
     */
    public function testFacadeThrowsJobFailedException(): void
    {
        $id = 'test';
        $this->expectException(JobFailedException::class);
        $job = new TestExceptionJob();
        $job->prepareResponse($id);
        app(Dispatcher::class)->dispatch($job);

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        LaravelJobResponse::throwExceptionOnFailure(true);
        $response = LaravelJobResponse::awaitResponse($job, 1);
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('queue.default', 'database');
        $app['config']->set('cache.default', 'database');
        $app['config']->set('job-response.transport', 'cache');
    }
}
