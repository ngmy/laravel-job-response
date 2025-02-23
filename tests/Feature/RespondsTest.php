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
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestExceptionJob;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestJob;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestLongRunningJob;
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

    #[Group('failing')]
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
        $jobs->each(static function (TestJob $job) use ($ident): void {
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
