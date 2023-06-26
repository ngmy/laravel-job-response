<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Feature;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Williamjulianvicary\LaravelJobResponse\Exceptions\TimeoutException;
use Williamjulianvicary\LaravelJobResponse\Facades\LaravelJobResponse;
use Williamjulianvicary\LaravelJobResponse\Response;
use Williamjulianvicary\LaravelJobResponse\ResponseCollection;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestJob;
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
final class RedisTransportTest extends TestCase
{
    public function testJobTransportSuccess(): void
    {
        $job = new TestJob();
        $response = $job->awaitResponse(10);

        self::assertInstanceOf(Response::class, $response);
    }

    public function testMultipleJobSuccess(): void
    {
        Config::set('queue.default', 'database');
        $ident = LaravelJobResponse::generateIdent();

        $jobs = [new TestJob(), new TestJob()];
        $jobs = collect($jobs)->map(function (TestJob $job) use ($ident): void {
            $job->prepareResponse($ident);
            App::make(Dispatcher::class)->dispatch($job);
        });

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        $responses = App::make(TransportContract::class)->awaitResponses($ident, 2, 5);

        self::assertInstanceOf(ResponseCollection::class, $responses);
        self::assertCount(2, $responses);
    }

    public function testTimeoutOnNoQueueResponse(): void
    {
        $this->expectException(TimeoutException::class);
        App::make(TransportContract::class)->awaitResponse('dummy', 1);
    }

    public function testTimeOnNoResponseMultipleResponses(): void
    {
        Config::set('queue.default', 'database');
        $this->expectException(TimeoutException::class);

        App::make(TransportContract::class)->awaitResponses('dummy', 3, -1);
    }

    protected function getEnvironmentSetUp($app): void
    {
        $config = $app['config'];
        \assert($config instanceof Repository);
        $config->set('job-response.transport', 'redis');
        $config->set('queue.default', 'sync');
    }
}
