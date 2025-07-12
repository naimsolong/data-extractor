<?php

namespace NaimSolong\DataExtractor\Commands;

use Illuminate\Console\Command;
use NaimSolong\DataExtractor\Dto\Export;

class DataExtractCommand extends Command
{
    public $signature = 'data:extract';

    public $description = 'Extract data based on predefined options';

    public array $options = [];

    public function __construct()
    {
        $this->options = config('data-extractor.options', []);

        parent::__construct();
    }

    public function handle(): int
    {
        if (! config('data-extractor.allow_production') && app()->environment('production')) {
            $this->error('Data extraction is not allowed in production environment.');

            return self::FAILURE;
        }

        if ($this->validateOptions() === false) {
            return self::FAILURE;
        }

        $selectedOptions = $this->promptOptions();

        $id = $this->promptModelId($selectedOptions);

        if ($id <= 0) {
            return self::FAILURE;
        }

        $selectedSource = $selectedOptions['source'] ?? null;
        if (! $selectedSource) {
            $this->error('No source specified in the selected option.');

            return self::FAILURE;
        }
        $source = config("data-extractor.source.$selectedSource", []);

        $query = app($source['model'])
            ->setConnection($source['connection'] ?? 'mysql')
            ->with($source['relationships'] ?? [])
            ->where('id', $id);

        if (! $query->exists()) {
            $this->error("No record found with ID {$id} in the {$source['model']} model.");

            return self::FAILURE;
        }

        $data = $query->first();

        $insertSql = $this->generateInsertSql($data);

        $this->line("<info>Generated SQL Insert Statement:</info>\n$insertSql");

        // Get loaded relationships
        $loadedRelations = $data->getRelations();

        // Debug: Show loaded relations context
        $this->line('<comment>Loaded Relations:</comment>');
        foreach ($loadedRelations as $relationName => $relationData) {
            $this->line("  - {$relationName}: ".(is_countable($relationData) ? count($relationData).' records' : 'single record'));

            $this->line("<comment>Generated SQL for relation '{$relationName}':</comment>");
            if (is_countable($relationData)) {
                foreach ($relationData as $relation) {
                    $insertSql = $this->generateInsertSql($relation);
                    $this->line("\n$insertSql");
                }
            } else {
                $insertSql = $this->generateInsertSql($relationData);
                $this->line("\n$insertSql");
            }
        }

        return self::SUCCESS;
    }

    protected function validateOptions(): bool
    {
        if (empty($this->options)) {
            $this->error('No options found in the configuration.');

            return false;
        }

        $sourceConnections = array_keys(config('data-extractor.source'));

        foreach ($this->options as $option) {
            if (empty($option['name']) || empty($option['source']) || empty($option['export'])) {
                $this->error('Invalid option format. Each option must have a name, source, and export configuration.');

                return false;
            }

            if (! in_array($option['export']['format'], Export::FORMATS)) {
                $this->error('Invalid export format in option: '.$option['name']);

                return false;
            }

            if (! isset($option['source']) || ! is_string($option['source']) || ! in_array($option['source'], $sourceConnections)) {
                $this->error('Invalid source specified in option: '.$option['name']);

                return false;
            }

            $selectedSource = config("data-extractor.source.{$option['source']}", []);

            if (! isset($selectedSource['model'])) {
                $this->error('Invalid model configuration in source: '.$option['source']);

                return false;
            }
        }

        return true;
    }

    protected function promptOptions(): array
    {
        $this->table(
            ['Name', 'Description', 'Export Format'],
            array_map(function ($option) {
                return [
                    $option['name'],
                    $option['description'] ?? 'N/A',
                    $option['export']['format'],
                ];
            }, $this->options)
        );

        $optionNames = array_column($this->options, 'name');

        $selectedKey = array_keys($this->choice(
            'Select an option to execute',
            $optionNames,
            null,
            null,
            true
        ));

        return $this->options[$selectedKey[0]];
    }

    protected function promptModelId($option): int
    {
        $source = $option['source'] ?? null;

        if (! $source || ! is_string($source)) {
            $this->error('Invalid source specified in the option.');

            return 0;
        }

        $modelClass = config("data-extractor.source.$source.model");

        if (! $modelClass || ! class_exists($modelClass)) {
            $this->error('Invalid model class specified in the option source.');

            return 0;
        }

        $modelInstance = new $modelClass;

        $id = $this->ask("Enter the ID of the {$modelInstance->getTable()} to extract data from");

        return (int) $id;
    }

    protected function generateInsertSql($data): string
    {
        $insertString = [];
        $dataArray = $data->toArray();
        foreach ($dataArray as $key => $value) {
            if (is_array($value)) {
                $insertString[] = "`$key` = '".json_encode($value, JSON_UNESCAPED_UNICODE)."'";
            } elseif (is_null($value)) {
                $insertString[] = "`$key` = NULL";
            } elseif (is_numeric($value)) {
                $insertString[] = "`$key` = $value";
            } elseif (is_bool($value)) {
                $insertString[] = "`$key` = ".($value ? '1' : '0');
            } else {
                $insertString[] = "`$key` = '".addslashes($value)."'";
            }
        }

        return "INSERT INTO `{$data->getTable()}` VALUE (".implode(', ', $insertString).');';
    }
}
