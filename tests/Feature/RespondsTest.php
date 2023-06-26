<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Feature;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Williamjulianvicary\LaravelJobResponse\ExceptionResponse;
use Williamjulianvicary\LaravelJobResponse\Exceptions\TimeoutException;
use Williamjulianvicary\LaravelJobResponse\Facades\LaravelJobResponse;
use Williamjulianvicary\LaravelJobResponse\Response;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestExceptionJob;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestJob;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestLongRunningJob;
use Williamjulianvicary\LaravelJobResponse\Tests\TestCase;
use Williamjulianvicary\LaravelJobResponse\Transport\TransportContract;

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
final class RespondsTest extends TestCase
{
    public function testSingleResponseSuccess(): void
    {
        $job = new TestJob();
        $job->prepareResponse();

        App::make(Dispatcher::class)->dispatch($job);

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        $response = App::make(TransportContract::class)->awaitResponse($job->getResponseIdent(), 1);
        \assert($response instanceof Response);
        self::assertInstanceOf(Response::class, $response);
        self::assertTrue($response->getData());
    }

    /**
     * @group failing
     */
    public function testSingleResponseException(): void
    {
        $job = new TestExceptionJob();
        $job->prepareResponse();

        App::make(Dispatcher::class)->dispatch($job);

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        $response = App::make(TransportContract::class)->awaitResponse($job->getResponseIdent(), 2);
        \assert($response instanceof ExceptionResponse);

        self::assertInstanceOf(ExceptionResponse::class, $response);
    }

    public function testThreeResponses(): void
    {
        $ident = LaravelJobResponse::generateIdent();

        $jobs = collect([new TestJob(), new TestJob(), new TestJob()]);
        $jobs->each(function (TestJob $job) use ($ident): void {
            $job->prepareResponse($ident);
            App::make(Dispatcher::class)->dispatch($job);
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

        $responses = App::make(TransportContract::class)->awaitResponses($ident, 3, 5);

        self::assertCount(3, $responses);
    }

    public function testJobTimeOut(): void
    {
        $this->expectException(TimeoutException::class);
        $job = new TestLongRunningJob();
        $job->prepareResponse();

        App::make(Dispatcher::class)->dispatch($job);

        // Note: We're mocking a long-running job here by not actually running the queue.
        // As the queue does not finish, it is impossible for the job to respond within the timeout.
        // This is to avoid the lack of  multi-threading in PHPUNIT (i.e we cannot run the await before running the queue worker).

        App::make(TransportContract::class)->awaitResponse($job->getResponseIdent(), 1);
    }

    protected function getEnvironmentSetUp($app): void
    {
        $config = $app['config'];
        \assert($config instanceof Repository);
        $config->set('queue.default', 'database');
        $config->set('cache.default', 'database');
        $config->set('job-response.transport', 'cache');
    }
}
