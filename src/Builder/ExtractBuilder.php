<?php

namespace NaimSolong\DataExtractor\Builder;

use Exception;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;

class ExtractBuilder
{
    public const FORMAT_CSV = 'csv';

    public const FORMAT_SQL = 'sql';

    public const DEFAULT_FORMAT = self::FORMAT_SQL;

    public const FORMATS = [
        self::FORMAT_CSV,
        self::FORMAT_SQL,
    ];

    /**
     * The model instance.
     */
    protected mixed $model;

    protected CsvBuilder|SqlBuilder $builder;

    public function createBuilder(string $type): self
    {
        if (! in_array($type, self::FORMATS, true)) {
            throw new InvalidArgumentException("Invalid builder type: {$type}");
        }

        $this->builder = match ($type) {
            self::FORMAT_CSV => new CsvBuilder,
            self::FORMAT_SQL => new SqlBuilder,
            default => throw new InvalidArgumentException("Unsupported builder type: {$type}"),
        };

        return $this;
    }

    public function asCsv(): self
    {
        return $this->createBuilder(self::FORMAT_CSV);
    }

    public function asSql(): self
    {
        return $this->createBuilder(self::FORMAT_SQL);
    }

    /**
     * Get the model instance.
     */
    public function getModel(): mixed
    {
        return $this->model;
    }

    /**
     * Set the model instance.
     */
    public function setModel(mixed $model): self
    {
        if (! is_subclass_of($model, Model::class)) {
            throw new Exception('The provided model, parent must be an instance of Illuminate\Database\Eloquent\Model');
        }

        $this->model = $model;

        return $this;
    }

    protected function getTableName(): string
    {
        return $this->model->getTable();
    }

    protected function getTableColumns(): array
    {
        return app(DatabaseManager::class)
            ->connection($this->model->getConnectionName())
            ->getSchemaBuilder()
            ->getColumnListing($this->getTableName());
    }

    public function build(): string
    {
        if (! isset($this->builder)) {
            throw new RuntimeException('Builder not initialized. Call createBuilder(), asCsv() or asSql() first.');
        }

        if (! isset($this->model)) {
            throw new RuntimeException('Model not set. Use setModel() to set the model before building.');
        }

        $table = $this->getTableName();

        $columns = $this->getTableColumns();

        $this->builder
            ->setSchemaName($table)
            ->setColumns($columns)
            ->setData($this->model->toArray());

        return $this->builder->build();
    }
}
