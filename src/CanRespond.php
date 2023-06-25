<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse;

use Illuminate\Support\Facades\App;
use Williamjulianvicary\LaravelJobResponse\Facades\LaravelJobResponse;
use Williamjulianvicary\LaravelJobResponse\Transport\TransportContract;

trait CanRespond
{
    public string $responseIdent;

    public function prepareResponse(?string $id = null): self
    {
        // Don't re-prepare the response if it has already been prepared.
        if (!isset($this->responseIdent)) {
            $this->responseIdent = $id ?? LaravelJobResponse::generateIdent(self::class);
        }

        return $this;
    }

    public function failed(?\Throwable $exception = null): void
    {
        $this->respondWithException($exception);
    }

    public function respond(mixed $data): void
    {
        App::make(TransportContract::class)->respond($this->getResponseIdent(), $data);
    }

    public function respondWithException(?\Throwable $exception = null): void
    {
        App::make(TransportContract::class)->handleFailure($this->getResponseIdent(), $exception);
    }

    public function getResponseIdent(): string
    {
        return $this->responseIdent;
    }

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
    public function awaitResponse(int $timeout = 10, bool $throwException = false): ResponseContract
    {
        return LaravelJobResponse::throwExceptionOnFailure($throwException)->awaitResponse($this, $timeout);
    }
}
