# Laravel Job Response - Making your jobs respond

[![Latest Stable Version](https://img.shields.io/packagist/v/ngmy/laravel-job-response.svg?style=flat-square&label=stable)](https://packagist.org/packages/ngmy/laravel-job-response)
[![Test Status](https://img.shields.io/github/actions/workflow/status/ngmy/laravel-job-response/test.yml?style=flat-square&label=test)](https://github.com/ngmy/laravel-job-response/actions/workflows/test.yml)
[![Lint Status](https://img.shields.io/github/actions/workflow/status/ngmy/laravel-job-response/lint.yml?style=flat-square&label=lint)](https://github.com/ngmy/laravel-job-response/actions/workflows/lint.yml)
[![Code Coverage](https://img.shields.io/coverallsCoverage/github/ngmy/laravel-job-response?style=flat-square)](https://coveralls.io/github/ngmy/laravel-job-response)
[![Total Downloads](https://img.shields.io/packagist/dt/ngmy/laravel-job-response.svg?style=flat-square)](https://packagist.org/packages/ngmy/laravel-job-response)

Have you ever needed to run a Laravel job (or multiple jobs), wait for the response and then use that response? This is
exactly the functionality this package provides.

## Installation

You can install the package via composer:

```bash
composer require ngmy/laravel-job-response
```

## Requirements

- PHP >= 8.1
- Laravel >= 9.0

## Usage

In your `Job` use the `CanRespond` trait and add implement the `JobCanRespond` contract.

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Williamjulianvicary\LaravelJobResponse\CanRespond;
use Williamjulianvicary\LaravelJobResponse\Contracts\JobCanRespond;

class TestJob implements ShouldQueue, JobCanRespond
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, CanRespond;

    public function handle(): void
    {
        $this->respond('Success');
    }
}
```

Then in your Service/Controller/elsewhere, await a response from your job.

```php
<?php

namespace App\Services;

use App\Jobs\TestJob;
use Williamjulianvicary\LaravelJobResponse\ExceptionResponse;
use Williamjulianvicary\LaravelJobResponse\Response;

class Service
{
    public function test(): void
    {
        $job = new TestJob();
        /** @var ExceptionResponse|Response $response */
        $response = $job->awaitResponse();

        if ($response instanceof ExceptionResponse) {
            echo $response->getMessage().PHP_EOL; // The exception message string thrown by the job.
        } else {
            echo $response->getData().PHP_EOL; // 'Success'
        }
    }
}
```

Or alternatively, run multiple jobs and await the responses.

```php
<?php

namespace App\Services;

use App\Jobs\TestJob;
use Williamjulianvicary\LaravelJobResponse\ExceptionResponse;
use Williamjulianvicary\LaravelJobResponse\Facades\LaravelJobResponse;
use Williamjulianvicary\LaravelJobResponse\Response;
use Williamjulianvicary\LaravelJobResponse\ResponseCollection;

class Service
{
    public function test(): void
    {
        $jobs = [new TestJob(), new TestJob()];
        /** @var ResponseCollection<array-key, ExceptionResponse|Response> $responses */
        $responses = LaravelJobResponse::awaitResponses($jobs);

        foreach ($responses as $response) {
            if ($response instanceof ExceptionResponse) {
                echo $response->getMessage().PHP_EOL;
            } else {
                echo $response->getData().PHP_EOL;
            }
        }
    }
}
```

### Responses

By default, the package responds in three ways:

- `ResponseCollection` - When multiple responses are expected, a ResponseCollection will be returned containing
  `Response` and/or `ExceptionResponse` objects.
- `Response` - A successful response object.
- `ExceptionResponse` - When a job fails the exception is caught and passed back.

### (Optional) Handling Exceptions

By default a `ExceptionResponse` object is created. However, this can lead to some extra boilerplate code to handle
this, so instead we've an optional method available that will re-throw these exceptions.

To enable this, use the Facade to update the `throwExceptionOnFailure` flag

```php
use Williamjulianvicary\LaravelJobResponse\Facades\LaravelJobResponse;

LaravelJobResponse::throwExceptionOnFailure(true);
```

Now whenever a await is issued, if an exception is encountered from the job, a `JobFailedException` will be raised:

```php
<?php

namespace App\Services;

use App\Jobs\TestJob;
use Williamjulianvicary\LaravelJobResponse\Exceptions\JobFailedException;
use Williamjulianvicary\LaravelJobResponse\Facades\LaravelJobResponse;

class Service
{
    public function test(): void
    {
        $jobs = [new TestJob(), new TestJob()];
        try {
            $responses = LaravelJobResponse::awaitResponses($jobs);
        } catch (JobFailedException $exception) {
            // One of the jobs failed.
            $exception->getTrace(); // The exception trace string thrown by the job.
        }
    }
}
```

### Methods

```php
// Methods available on your jobs

// Await a response for this job, optionally accepts a timeout and bool whether a exception should be raised if the job fails.
// Responds with either Response or ExceptionResponse objects.
$job->awaitResponse(int $timeout = 10, bool $throwException = false): ExceptionResponse|Response;

// Should be used within the handle() method of the job to respond appropriately.
$job->respond(mixed $data): void;

// If you override the failed() method, this method responds with an exception.
$job->respondWithException(?\Throwable $exception = null): void;

// Facade methods

// Await a response for the given job.
LaravelJobResponse::awaitResponse(JobCanRespond $job, int $timeout = 10): ExceptionResponse|Response;

// Await responses from the provided job array.
LaravelJobResponse::awaitResponses(JobCanRespond[] $jobs, int $timeout = 10): ResponseCollection<array-key, ExceptionResponse|Response>;

// Change how exceptions are handled (see above).
LaravelJobResponse::throwExceptionOnFailure(bool $flag = false): self;
```

### Troubleshooting

There are a few quirks within Laravel that you may run into with this package.

- When running with a `sync` driver, Exceptions will not be caught - this is because Laravel does not natively catch
  them with the Sync driver and it is impossible for our package to pick them up. If you need to handle exceptions with
  this driver, use `$job->fail($exception);` instead.

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [William Julian-Vicary](https://github.com/williamjulianvicary)
- [Yuta Nagamiya](https://github.com/ngmy)
- [All Contributors](https://github.com/ngmy/laravel-job-response/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
