<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Facades;

use Illuminate\Support\Facades\Facade;
use Williamjulianvicary\LaravelJobResponse\Contracts\JobCanRespond;
use Williamjulianvicary\LaravelJobResponse\ExceptionResponse;
use Williamjulianvicary\LaravelJobResponse\Response;
use Williamjulianvicary\LaravelJobResponse\ResponseCollection;

/**
 * @method static string                                                    generateIdent(string $class = null)
 * @method static self                                                      throwExceptionOnFailure(bool $flag = false)
 * @method static ExceptionResponse|Response                                awaitResponse(JobCanRespond $job, int $timeout = 10)
 * @method static ResponseCollection<array-key, ExceptionResponse|Response> awaitResponses(JobCanRespond[] $jobs, int $timeout = 10)
 */
class LaravelJobResponse extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-job-response';
    }
}
