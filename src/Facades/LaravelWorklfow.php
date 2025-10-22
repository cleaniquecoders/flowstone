<?php

namespace CleaniqueCoders\LaravelWorklfow\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CleaniqueCoders\LaravelWorklfow\LaravelWorklfow
 */
class LaravelWorklfow extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CleaniqueCoders\LaravelWorklfow\LaravelWorklfow::class;
    }
}
