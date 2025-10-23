<?php

namespace CleaniqueCoders\Flowstone\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CleaniqueCoders\Flowstone\Flowstone
 */
class Flowstone extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CleaniqueCoders\Flowstone\Flowstone::class;
    }
}
