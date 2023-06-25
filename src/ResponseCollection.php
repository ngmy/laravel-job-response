<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse;

use Illuminate\Support\Collection;

/**
 * @template TKey of array-key
 * @template TValue of ResponseContract
 *
 * @extends Collection<TKey, TValue>
 */
class ResponseCollection extends Collection
{
}
