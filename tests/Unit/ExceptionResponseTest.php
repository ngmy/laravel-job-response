<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Unit;

use Williamjulianvicary\LaravelJobResponse\ExceptionResponse;
use Williamjulianvicary\LaravelJobResponse\Tests\Data\TestException;
use Williamjulianvicary\LaravelJobResponse\Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
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
