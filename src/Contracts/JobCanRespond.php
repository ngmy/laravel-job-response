<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Contracts;

use Williamjulianvicary\LaravelJobResponse\ExceptionResponse;
use Williamjulianvicary\LaravelJobResponse\Response;
use Williamjulianvicary\LaravelJobResponse\ResponseContract;

interface JobCanRespond
{
    public function prepareResponse(?string $id = null): self;

    public function failed(?\Throwable $exception = null): void;

    public function respond(mixed $data): void;

    public function respondWithException(?\Throwable $exception = null): void;

    public function getResponseIdent(): string;

    /**
     * Dispatch the current job class and await a response.
     *
     * @param int  $timeout        default waits 10 seconds for a response
     * @param bool $throwException should we throw an exception on failures?
     *
     * @return ExceptionResponse|Response
     *
     * @phpstan-return ($throwException is false ? ExceptionResponse|Response : Response)
     */
    public function awaitResponse(int $timeout = 10, bool $throwException = false): ResponseContract;
}
