<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Str;
use Williamjulianvicary\LaravelJobResponse\Contracts\JobCanRespond;
use Williamjulianvicary\LaravelJobResponse\Transport\TransportContract;

class LaravelJobResponse
{
    public bool $throwExceptionOnFailure = false;

    public function generateIdent(string $class = null): string
    {
        return ($class ?? self::class).':rpc:'.Str::random(80);
    }

    public function throwExceptionOnFailure(bool $flag = false): self
    {
        $this->throwExceptionOnFailure = $flag;

        return $this;
    }

    public function awaitResponse(JobCanRespond $job, int $timeout = 10): ResponseContract
    {
        // Dispatch the job
        $job->prepareResponse();
        app(Dispatcher::class)->dispatch($job);

        return app(TransportContract::class)
            ->throwExceptionOnFailure($this->throwExceptionOnFailure)
            ->awaitResponse($job->getResponseIdent(), $timeout)
        ;
    }

    public function awaitResponses(array $jobs, int $timeout = 10): ResponseCollection
    {
        $queueIdent = $this->generateIdent();

        $jobCollection = collect($jobs);
        $jobCollection->each(static function (JobCanRespond $job) use ($queueIdent): void {
            $job->prepareResponse($queueIdent);
            app(Dispatcher::class)->dispatch($job);
        });

        return app(TransportContract::class)
            ->throwExceptionOnFailure($this->throwExceptionOnFailure)
            ->awaitResponses($queueIdent, $jobCollection->count(), $timeout)
        ;
    }
}
