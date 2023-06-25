<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse;

class Response implements ResponseContract
{
    private readonly mixed $data;

    public function __construct(mixed $data = null)
    {
        $this->data = $data;
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}
