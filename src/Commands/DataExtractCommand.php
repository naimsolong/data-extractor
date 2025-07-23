<?php

namespace NaimSolong\DataExtractor\Commands;

use Illuminate\Console\Command;
use NaimSolong\DataExtractor\Builder\ExtractBuilder;
use NaimSolong\DataExtractor\Extract;
use NaimSolong\DataExtractor\OptionsResolver;

class DataExtractCommand extends Command
{
    public $signature = 'data:extract
                        {--name= : The name of the option to execute}
                        {--queryId= : The ID of the model to extract data from}
                        {--format= : The format of the extracted data (csv, sql)}';

    public $description = 'Extract data based on predefined options';

    public OptionsResolver $options;

    public function __construct()
    {
        $this->options = new OptionsResolver;

        parent::__construct();
    }

    public function handle(): int
    {
        if (! config('data-extractor.is_enabled')) {
            $this->error('Data extraction is not enabled.');

            return self::FAILURE;
        }

        $name = $this->option('name');
        $queryId = $this->option('queryId');
        $format = $this->option('format');

        $name != null ? $this->options->set($name) : $this->promptOptions();

        $id = $queryId ?? $this->promptModelId();

        $format = $format ?: ExtractBuilder::DEFAULT_FORMAT;

        if ($id <= 0) {
            return self::FAILURE;
        }

        $results = (new Extract)
            ->option($this->options->get()->toArray()['name'])
            ->queryId($id)
            ->extract($format);

        foreach ($results as $result) {
            $this->line($result);
        }

        return self::SUCCESS;
    }

    protected function promptOptions(): array
    {
        $options = $this->options->all();

        $this->table(
            ['Name', 'Description'],
            array_map(function ($option) {
                $option = $option->toArray();

                return [
                    $option['name'],
                    $option['description'],
                ];
            }, $options)
        );

        $optionNames = array_column($options, 'name');

        $selectedKey = array_keys($this->choice(
            'Select an option to execute',
            $optionNames,
            null,
            null,
            true
        ))[0];

        $this->options->set($selectedKey);

        return $this->options->get()->toArray();
    }

    protected function promptModelId(): int
    {
        $source = $this->options->source();

        $modelClass = $source->toArray()['model'];

        if (! $modelClass || ! class_exists($modelClass)) {
            $this->error('Invalid model class specified in the option source.');

            return 0;
        }

        $modelInstance = new $modelClass;

        $id = $this->ask("Enter the ID of the {$modelInstance->getTable()} to extract data from");

        return (int) $id;
    }
}
