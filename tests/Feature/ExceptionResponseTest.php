<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Feature;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Williamjulianvicary\LaravelJobResponse\CanRespond;
use Williamjulianvicary\LaravelJobResponse\ExceptionResponse;
use Williamjulianvicary\LaravelJobResponse\Exceptions\JobFailedException;
use Williamjulianvicary\LaravelJobResponse\Exceptions\TimeoutException;
use Williamjulianvicary\LaravelJobResponse\Facades\LaravelJobResponse;
use Williamjulianvicary\LaravelJobResponse\LaravelJobResponseServiceProvider;
use Williamjulianvicary\LaravelJobResponse\Response;
use Williamjulianvicary\LaravelJobResponse\ResponseCollection;
use Williamjulianvicary\LaravelJobResponse\ResponseFactory;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestException;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestExceptionJob;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestManuallyFailedJob;
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
final class ExceptionResponseTest extends TestCase
{
    #[Group('failing')]
    public function testThrowsJobFailedException(): void
    {
        $this->expectException(JobFailedException::class);
        $job = new TestExceptionJob();
        $job->prepareResponse();

        App::make(Dispatcher::class)->dispatch($job);

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        App::make(TransportContract::class)->throwExceptionOnFailure(true)->awaitResponse($job->getResponseIdent(), 1);
    }

    public function testManuallyFailedJob(): void
    {
        $job = new TestManuallyFailedJob();
        $job->prepareResponse();

        App::make(Dispatcher::class)->dispatch($job);

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        $response = App::make(TransportContract::class)->throwExceptionOnFailure(false)->awaitResponse($job->getResponseIdent(), 1);
        \assert($response instanceof ExceptionResponse);

        self::assertInstanceOf(ExceptionResponse::class, $response);
        self::assertNull($response->getMessage());
    }

    public function testThrowsJobFailedExceptionGetter(): void
    {
        try {
            $job = new TestExceptionJob();
            $job->prepareResponse();

            App::make(Dispatcher::class)->dispatch($job);

            Artisan::call('queue:work', [
                '--once' => 1,
            ]);

            App::make(TransportContract::class)->throwExceptionOnFailure(true)->awaitResponse($job->getResponseIdent(), 1);
        } catch (JobFailedException $e) {
            $exceptionResponse = $e->getExceptionResponse();
            self::assertInstanceOf(ExceptionResponse::class, $exceptionResponse);

            return;
        }

        self::fail('JobFailedException was not thrown.');
    }

    #[Group('failing')]
    public function testDoesNotThrowJobFailedException(): void
    {
        $job = new TestExceptionJob();
        $job->prepareResponse();

        App::make(Dispatcher::class)->dispatch($job);

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        $response = App::make(TransportContract::class)->throwExceptionOnFailure(false)->awaitResponse($job->getResponseIdent(), 1);
        \assert($response instanceof ExceptionResponse);
        self::assertInstanceOf(ExceptionResponse::class, $response);
        self::assertSame('TestException', $response->getExceptionBaseName());
        self::assertSame(TestException::class, $response->getExceptionClass());
    }

    #[Group('new')]
    public function testFacadeThrowsJobFailedException(): void
    {
        $id = 'test';
        $this->expectException(JobFailedException::class);
        $job = new TestExceptionJob();
        $job->prepareResponse($id);
        App::make(Dispatcher::class)->dispatch($job);

        Artisan::call('queue:work', [
            '--once' => 1,
        ]);

        LaravelJobResponse::throwExceptionOnFailure(true);
        LaravelJobResponse::awaitResponse($job, 1);
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
