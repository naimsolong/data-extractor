<?php

namespace naimsolong\DataExtractor;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use naimsolong\DataExtractor\Commands\DataExtractorCommand;

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
            ->hasViews()
            ->hasMigration('create_data_extractor_table')
            ->hasCommand(DataExtractorCommand::class);
    }
}
