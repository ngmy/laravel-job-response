<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Transport;

use Williamjulianvicary\LaravelJobResponse\ExceptionResponse;
use Williamjulianvicary\LaravelJobResponse\Exceptions\JobFailedException;
use Williamjulianvicary\LaravelJobResponse\Exceptions\TimeoutException;
use Williamjulianvicary\LaravelJobResponse\Response;
use Williamjulianvicary\LaravelJobResponse\ResponseCollection;
use Williamjulianvicary\LaravelJobResponse\ResponseContract;

interface TransportContract
{
    /**
     * @return ExceptionResponse|Response
     *
     * @throws TimeoutException
     * @throws JobFailedException
     */
    public function awaitResponse(string $id, int $timeout): ResponseContract;

    /**
     * @param string $id                the ID to lookup the job (should match the job ident)
     * @param int    $expectedResponses number of responses to expect
     * @param int    $timeout           timeout for the request
     *
     * @return ResponseCollection<array-key, ExceptionResponse|Response>
     *
     * @throws TimeoutException
     * @throws JobFailedException
     */
    public function awaitResponses(string $id, int $expectedResponses, int $timeout): ResponseCollection;

    public function throwExceptionOnFailure(bool $flag = false): static;

    public function handleFailure(string $id, ?\Throwable $exception = null): void;

    public function respond(string $id, mixed $data): void;
}
