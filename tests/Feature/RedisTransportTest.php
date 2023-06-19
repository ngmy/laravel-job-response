<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Feature;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Williamjulianvicary\LaravelJobResponse\Exceptions\TimeoutException;
use Williamjulianvicary\LaravelJobResponse\LaravelJobResponse;
use Williamjulianvicary\LaravelJobResponse\Response;
use Williamjulianvicary\LaravelJobResponse\ResponseCollection;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestJob;
use Williamjulianvicary\LaravelJobResponse\Tests\TestCase;
use Williamjulianvicary\LaravelJobResponse\Transport\TransportContract;

/**
 * @internal
 *
 * @coversNothing
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
        $ident = app(LaravelJobResponse::class)->generateIdent();

        $jobs = [new TestJob(), new TestJob()];
        $jobs = collect($jobs)->map(function ($job) use ($ident): void {
            $job->prepareResponse($ident);
            app(Dispatcher::class)->dispatch($job);
        });

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        $responses = app(TransportContract::class)->awaitResponses($ident, 2, 5);

        self::assertInstanceOf(ResponseCollection::class, $responses);
        self::assertCount(2, $responses);
    }

    public function testTimeoutOnNoQueueResponse(): void
    {
        $this->expectException(TimeoutException::class);
        app(TransportContract::class)->awaitResponse('dummy', 1);
    }

    public function testTimeOnNoResponseMultipleResponses(): void
    {
        Config::set('queue.default', 'database');
        $this->expectException(TimeoutException::class);

        app(TransportContract::class)->awaitResponses('dummy', 3, -1);
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('job-response.transport', 'redis');
        $app['config']->set('queue.default', 'sync');
    }
}
