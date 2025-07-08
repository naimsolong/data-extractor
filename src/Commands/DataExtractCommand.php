<?php

namespace NaimSolong\DataExtractor\Commands;

use Illuminate\Console\Command;
use NaimSolong\DataExtractor\Dto\Export;

class DataExtractCommand extends Command
{
    public $signature = 'data:extract';

    public $description = 'Extract data based on predefined instructions';

    public array $instructions = [];

    public function __construct()
    {
        $this->instructions = config('data-extractor.instructions', []);

        parent::__construct();
    }

    public function handle(): int
    {
        if (! config('data-extractor.allow_production') && app()->environment('production')) {
            $this->error('Data extraction is not allowed in production environment.');

            return self::FAILURE;
        }

        if ($this->validateInstructions() === false) {
            return self::FAILURE;
        }

        $selectedInstructions = $this->promptInstructions();

        $id = $this->promptModelId($selectedInstructions);

        if ($id <= 0) {
            return self::FAILURE;
        }

        $selectedSource = $selectedInstructions['source'] ?? null;
        if (! $selectedSource) {
            $this->error('No source specified in the selected instruction.');

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

    protected function validateInstructions(): bool
    {
        if (empty($this->instructions)) {
            $this->error('No instructions found in the configuration.');

            return false;
        }

        $sourceConnections = array_keys(config('data-extractor.source'));

        foreach ($this->instructions as $instruction) {
            if (empty($instruction['name']) || empty($instruction['source']) || empty($instruction['export'])) {
                $this->error('Invalid instruction format. Each instruction must have a name, source, and export configuration.');

                return false;
            }

            if (! in_array($instruction['export']['format'], Export::FORMATS)) {
                $this->error('Invalid export format in instruction: '.$instruction['name']);

                return false;
            }

            if (! isset($instruction['source']) || ! is_string($instruction['source']) || ! in_array($instruction['source'], $sourceConnections)) {
                $this->error('Invalid source specified in instruction: '.$instruction['name']);

                return false;
            }

            $selectedSource = config("data-extractor.source.{$instruction['source']}", []);

            if (! isset($selectedSource['model'])) {
                $this->error('Invalid model configuration in source: '.$instruction['source']);

                return false;
            }
        }

        return true;
    }

    protected function promptInstructions(): array
    {
        $this->table(
            ['Name', 'Description', 'Export Format'],
            array_map(function ($instruction) {
                return [
                    $instruction['name'],
                    $instruction['description'] ?? 'N/A',
                    $instruction['export']['format'],
                ];
            }, $this->instructions)
        );

        $instructionNames = array_column($this->instructions, 'name');

        $selectedKey = array_keys($this->choice(
            'Select an instruction to execute',
            $instructionNames,
            null,
            null,
            true
        ));

        return $this->instructions[$selectedKey[0]];
    }

    protected function promptModelId($instruction): int
    {
        $source = $instruction['source'] ?? null;

        if (! $source || ! is_string($source)) {
            $this->error('Invalid source specified in the instruction.');

            return 0;
        }

        $modelClass = config("data-extractor.source.$source.model");

        if (! $modelClass || ! class_exists($modelClass)) {
            $this->error('Invalid model class specified in the instruction source.');

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
