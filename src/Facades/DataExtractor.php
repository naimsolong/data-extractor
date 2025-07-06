<?php

namespace naimsolong\DataExtractor\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \naimsolong\DataExtractor\DataExtractor
 */
class DataExtractor extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \naimsolong\DataExtractor\DataExtractor::class;
    }
}
