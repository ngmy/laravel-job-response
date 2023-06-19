<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
// use Orchestra\Database\ConsoleServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Williamjulianvicary\LaravelJobResponse\LaravelJobResponseServiceProvider;

/**
 * @internal
 *
 * @coversNothing
 */
abstract class TestCase extends OrchestraTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(realpath(__DIR__.'/database/migrations'));
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param mixed $app
     */
    protected function getPackageProviders($app)
    {
        return [
            LaravelJobResponseServiceProvider::class,
        ];
    }
}
