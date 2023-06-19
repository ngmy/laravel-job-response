<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests\Data;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Williamjulianvicary\LaravelJobResponse\CanRespond;
use Williamjulianvicary\LaravelJobResponse\Contracts\JobCanRespond;

class TestLongRunningJob implements ShouldQueue, JobCanRespond
{
    use CanRespond;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public $sleep;

    public function __construct($sleep = 2)
    {
        $this->sleep = $sleep;
    }

    public function handle(): void
    {
        usleep($this->sleep * 1000 * 1000);
        $this->respond(true);
    }
}
