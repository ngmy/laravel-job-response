<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse;

class ResponseFactory
{
    /**
     * @param array{response?: mixed, exception?: array{
     *     exception_class: string,
     *     exception_basename: string,
     *     message: string,
     *     file: string,
     *     code: int,
     *     trace: string,
     *     line: int,
     * }|array{}} $response
     *
     * @return ExceptionResponse|Response
     */
    public static function create(array $response): ResponseContract
    {
        if (\array_key_exists('exception', $response)) {
            return new ExceptionResponse($response['exception']);
        }

        if (\array_key_exists('response', $response)) {
            return new Response($response['response']);
        }

        throw new \InvalidArgumentException('Response provided should be either exception or response type, neither provided.');
    }
}
