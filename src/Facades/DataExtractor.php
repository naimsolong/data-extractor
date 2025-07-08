<?php

namespace NaimSolong\DataExtractor\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \NaimSolong\DataExtractor\Extract
 */
class DataExtractor extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \NaimSolong\DataExtractor\Extract::class;
    }
}
