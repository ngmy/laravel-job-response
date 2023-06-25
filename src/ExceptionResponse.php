<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse;

class ExceptionResponse implements ResponseContract
{
    private readonly ?string $exceptionClass;
    private readonly ?string $exceptionBaseName;
    private readonly ?string $trace;
    private readonly ?string $message;
    private readonly ?int $code;
    private readonly ?int $line;
    private readonly ?string $file;

    /**
     * @param array{
     *     exception_class?: string,
     *     exception_basename?: string,
     *     message?: string,
     *     file?: string,
     *     code?: int,
     *     trace?: string,
     *     line?: int,
     * }|array{} $exception
     */
    public function __construct(array $exception)
    {
        $this->exceptionClass = $exception['exception_class'] ?? null;
        $this->exceptionBaseName = $exception['exception_basename'] ?? null;
        $this->trace = $exception['trace'] ?? null;
        $this->message = $exception['message'] ?? null;
        $this->code = $exception['code'] ?? null;
        $this->line = $exception['line'] ?? null;
        $this->file = $exception['file'] ?? null;
    }

    public function getExceptionClass(): ?string
    {
        return $this->exceptionClass;
    }

    public function getExceptionBaseName(): ?string
    {
        return $this->exceptionBaseName;
    }

    public function getTrace(): ?string
    {
        return $this->trace;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function getLine(): ?int
    {
        return $this->line;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }
}
