<?php

namespace NaimSolong\DataExtractor\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \NaimSolong\DataExtractor\DataExtractor
 */
class DataExtractor extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \NaimSolong\DataExtractor\DataExtractor::class;
    }
}
