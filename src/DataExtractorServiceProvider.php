<?php

namespace NaimSolong\DataExtractor;

use NaimSolong\DataExtractor\Commands\DataExtractorCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DataExtractorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('data-extractor')
            ->hasConfigFile()
            ->hasMigration('create_data_extractor_table')
            ->hasCommand(DataExtractorCommand::class);
    }
}
