<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use DatabaseTransactions;

    protected $enablesPackageDiscoveries = true;

    protected function setUp(): void
    {
        parent::setUp();
        \assert(false !== realpath(__DIR__.'/database/migrations'));
        $this->loadMigrationsFrom(realpath(__DIR__.'/database/migrations'));
    }
}
