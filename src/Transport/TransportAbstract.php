<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Transport;

use Williamjulianvicary\LaravelJobResponse\ExceptionResponse;
use Williamjulianvicary\LaravelJobResponse\Exceptions\JobFailedException;
use Williamjulianvicary\LaravelJobResponse\Exceptions\TimeoutException;
use Williamjulianvicary\LaravelJobResponse\Response;
use Williamjulianvicary\LaravelJobResponse\ResponseCollection;
use Williamjulianvicary\LaravelJobResponse\ResponseContract;
use Williamjulianvicary\LaravelJobResponse\ResponseFactory;

abstract class TransportAbstract
{
    /**
     * The maximum number of seconds cached items should be held for (before collection).
     * 2 minutes by default - in theory, this data should be collected within a few ms.
     */
    public int $cacheTtl = 120;

    /**
     * By default if an exception occurs the exception will be passed as a ExceptionResponse object, however if this
     * flag is true, an exception will be raised instead.
     */
    public bool $shouldThrowException = false;

    /**
     * @param array{response?: mixed, exception?: array{
     *     exception_class: string,
     *     exception_basename: string,
     *     message: string,
     *     file: string,
     *     code: int,
     *     trace: string,
     *     line: int,
     * }|array{}} $data
     *
     * @throws TimeoutException
     */
    abstract public function sendResponse(string $id, array $data): void;

    public function throwExceptionOnFailure(bool $flag = false): static
    {
        $this->shouldThrowException = $flag;

        return $this;
    }

    public function handleFailure(string $id, ?\Throwable $exception = null): void
    {
        $exceptionData = $exception instanceof \Throwable ? $this->exceptionToArray($exception) : [];
        $data = ['exception' => $exceptionData];
        $this->sendResponse($id, $data);
    }

    public function respond(string $id, mixed $data): void
    {
        $data = ['response' => $data];
        $this->sendResponse($id, $data);
    }

    /**
     * @param array{response?: mixed, exception?: array{
     *     exception_class: string,
     *     exception_basename: string,
     *     message: string,
     *     file: string,
     *     code: int,
     *     trace: string,
     *     line: int,
     * }|array{}} $responseData
     *
     * @return ExceptionResponse|Response
     *
     * @throws JobFailedException
     */
    protected function createResponse(array $responseData): ResponseContract
    {
        $response = ResponseFactory::create($responseData);
        if ($this->shouldThrowException && $response instanceof ExceptionResponse) {
            throw JobFailedException::fromExceptionResponse($response);
        }

        return $response;
    }

    /**
     * @param list<array{response?: mixed, exception?: array{
     *     exception_class: string,
     *     exception_basename: string,
     *     message: string,
     *     file: string,
     *     code: int,
     *     trace: string,
     *     line: int,
     * }|array{}}> $responses
     *
     * @return ResponseCollection<array-key, ExceptionResponse|Response>
     *
     * @throws JobFailedException
     */
    protected function createResponses(array $responses): ResponseCollection
    {
        /** @var ResponseCollection<array-key, ExceptionResponse|Response> $collection */
        $collection = new ResponseCollection();
        foreach ($responses as $response) {
            $collection->push($this->createResponse($response));
        }

        return $collection;
    }

    /**
     * @return array{
     *     exception_class: string,
     *     exception_basename: string,
     *     message: string,
     *     file: string,
     *     code: int,
     *     trace: string,
     *     line: int,
     * }
     */
    private function exceptionToArray(\Throwable $exception): array
    {
        return [
            'exception_class' => \get_class($exception),
            'exception_basename' => class_basename($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'code' => $exception->getCode(),
            'trace' => $exception->getTraceAsString(),
            'line' => $exception->getLine(),
        ];
    }
}
