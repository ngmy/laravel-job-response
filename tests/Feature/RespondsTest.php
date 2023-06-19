<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Feature;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Artisan;
use Williamjulianvicary\LaravelJobResponse\ExceptionResponse;
use Williamjulianvicary\LaravelJobResponse\Exceptions\TimeoutException;
use Williamjulianvicary\LaravelJobResponse\LaravelJobResponse;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestExceptionJob;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestJob;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestLongRunningJob;
use Williamjulianvicary\LaravelJobResponse\Tests\TestCase;
use Williamjulianvicary\LaravelJobResponse\Transport\TransportContract;

/**
 * @internal
 *
 * @coversNothing
 */
final class RespondsTest extends TestCase
{
    public function testSingleResponseSuccess(): void
    {
        $job = new TestJob();
        $job->prepareResponse();

        app(Dispatcher::class)->dispatch($job);

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        $response = app(TransportContract::class)->awaitResponse($job->getResponseIdent(), 1);
        self::assertTrue($response->getData());
    }

    /**
     * @group failing
     */
    public function testSingleResponseException(): void
    {
        $job = new TestExceptionJob();
        $job->prepareResponse();

        app(Dispatcher::class)->dispatch($job);

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        $response = app(TransportContract::class)->awaitResponse($job->getResponseIdent(), 2);

        self::assertInstanceOf(ExceptionResponse::class, $response);
    }

    public function testThreeResponses(): void
    {
        $ident = app(LaravelJobResponse::class)->generateIdent();

        $jobs = collect([new TestJob(), new TestJob(), new TestJob()]);
        $jobs->each(function (TestJob $job) use ($ident): void {
            $job->prepareResponse($ident);
            app(Dispatcher::class)->dispatch($job);
        });

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        $response = app(TransportContract::class)->awaitResponses($ident, 3, 5);

        self::assertCount(3, $response);
    }

    public function testJobTimeOut(): void
    {
        $this->expectException(TimeoutException::class);
        $job = new TestLongRunningJob();
        $job->prepareResponse();

        app(Dispatcher::class)->dispatch($job);

        // Note: We're mocking a long-running job here by not actually running the queue.
        // As the queue does not finish, it is impossible for the job to respond within the timeout.
        // This is to avoid the lack of  multi-threading in PHPUNIT (i.e we cannot run the await before running the queue worker).

        app(TransportContract::class)->awaitResponse($job->getResponseIdent(), 1);
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('queue.default', 'database');
        $app['config']->set('cache.default', 'database');
        $app['config']->set('job-response.transport', 'cache');
    }
}
