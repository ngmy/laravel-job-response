<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Feature;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
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
use Williamjulianvicary\LaravelJobResponse\Transport\TransportContract;
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
        $jobs = collect($jobs)->map(static function (TestJob $job) use ($ident): void {
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
