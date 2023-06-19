<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Unit;

use Williamjulianvicary\LaravelJobResponse\ExceptionResponse;
use Williamjulianvicary\LaravelJobResponse\Response;
use Williamjulianvicary\LaravelJobResponse\ResponseFactory;
use Williamjulianvicary\LaravelJobResponse\Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ResponseFactoryTest extends TestCase
{
    public function testExceptionThrownWhenIncorrectResponseTypeAttempted(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ResponseFactory::create(['test' => 'test']);
    }

    public function testExceptionResponseReturned(): void
    {
        $response = ['exception' => []];

        $response = ResponseFactory::create($response);
        self::assertInstanceOf(ExceptionResponse::class, $response);
    }

    public function testResponseReturned(): void
    {
        $response = ['response' => 'test'];

        $response = ResponseFactory::create($response);
        self::assertInstanceOf(Response::class, $response);
    }
}
