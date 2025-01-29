<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use Williamjulianvicary\LaravelJobResponse\CanRespond;
use Williamjulianvicary\LaravelJobResponse\ExceptionResponse;
use Williamjulianvicary\LaravelJobResponse\Exceptions\JobFailedException;
use Williamjulianvicary\LaravelJobResponse\Exceptions\TimeoutException;
use Williamjulianvicary\LaravelJobResponse\LaravelJobResponse;
use Williamjulianvicary\LaravelJobResponse\LaravelJobResponseServiceProvider;
use Williamjulianvicary\LaravelJobResponse\Response;
use Williamjulianvicary\LaravelJobResponse\ResponseCollection;
use Williamjulianvicary\LaravelJobResponse\ResponseFactory;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestException;
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
#[CoversClass(\Williamjulianvicary\LaravelJobResponse\Facades\LaravelJobResponse::class)]
#[CoversClass(LaravelJobResponse::class)]
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
    public function testExceptionResponseGetters(): void
    {
        $exception = new TestException();
        $data = [
            'exception_class' => \get_class($exception),
            'exception_basename' => class_basename($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'code' => $exception->getCode(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];

        $exceptionResponse = new ExceptionResponse($data);
        self::assertSame(TestException::class, $exceptionResponse->getExceptionClass());
        self::assertSame('TestException', $exceptionResponse->getExceptionBaseName());
        self::assertSame($exception->getMessage(), $exceptionResponse->getMessage());
        self::assertSame($exception->getFile(), $exceptionResponse->getFile());
        self::assertSame($exception->getCode(), $exceptionResponse->getCode());
        self::assertSame($exception->getTraceAsString(), $exceptionResponse->getTrace());
        self::assertSame($exception->getLine(), $exceptionResponse->getLine());
    }
}
