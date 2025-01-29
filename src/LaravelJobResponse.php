<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Str;
use Williamjulianvicary\LaravelJobResponse\Contracts\JobCanRespond;
use Williamjulianvicary\LaravelJobResponse\Transport\TransportContract;

class LaravelJobResponse
{
    private bool $throwExceptionOnFailure = false;

    public function __construct(
        private readonly Dispatcher $dispatcher,
        private readonly TransportContract $transport,
    ) {}

    public function generateIdent(?string $class = null): string
    {
        return ($class ?? self::class).':rpc:'.Str::random(80);
    }

    public function throwExceptionOnFailure(bool $flag = false): self
    {
        $this->throwExceptionOnFailure = $flag;

        return $this;
    }

    /**
     * @return ExceptionResponse|Response
     */
    public function awaitResponse(JobCanRespond $job, int $timeout = 10): ResponseContract
    {
        // Dispatch the job
        $job->prepareResponse();
        $this->dispatcher->dispatch($job);

        return $this->transport
            ->throwExceptionOnFailure($this->throwExceptionOnFailure)
            ->awaitResponse($job->getResponseIdent(), $timeout)
        ;
    }

    /**
     * @param JobCanRespond[] $jobs
     *
     * @return ResponseCollection<array-key, ExceptionResponse|Response>
     */
    public function awaitResponses(array $jobs, int $timeout = 10): ResponseCollection
    {
        $queueIdent = $this->generateIdent();

        $jobCollection = collect($jobs);
        $jobCollection->each(function (JobCanRespond $job) use ($queueIdent): void {
            $job->prepareResponse($queueIdent);
            $this->dispatcher->dispatch($job);
        });

        return $this->transport
            ->throwExceptionOnFailure($this->throwExceptionOnFailure)
            ->awaitResponses($queueIdent, $jobCollection->count(), $timeout)
        ;
    }
}
