<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Exceptions;

use Williamjulianvicary\LaravelJobResponse\ExceptionResponse;

class JobFailedException extends \Exception
{
    private readonly ExceptionResponse $exceptionResponse;

    public function __construct(ExceptionResponse $exceptionResponse, string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->exceptionResponse = $exceptionResponse;
    }

    public function getExceptionResponse(): ExceptionResponse
    {
        return $this->exceptionResponse;
    }

    public static function fromExceptionResponse(ExceptionResponse $exceptionResponse): self
    {
        return new self($exceptionResponse, 'Job Failed', 0, null);
    }
}
