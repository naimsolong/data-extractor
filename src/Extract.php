<?php

namespace NaimSolong\DataExtractor;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use NaimSolong\DataExtractor\Builder\ExtractBuilder;

class Extract
{
    protected int|array $queryId;

    protected array $datas = [];

    protected array $results = [];

    protected ?OptionsResolver $option = null;

    protected ?SourcesResolver $source = null;

    protected ExtractBuilder $builder;

    public function __construct()
    {
        if (! config('data-extractor.is_enabled')) {
            throw new Exception('Data Extractor is not enabled. Please check your configuration.');
        }

        $this->builder = new ExtractBuilder;
    }

    public function option(int|string $value): self
    {
        $this->option = (new OptionsResolver)->set($value);

        return $this;
    }

    public function source(string $source): self
    {
        $this->source = (new SourcesResolver)->set($source);

        return $this;
    }

    public function queryId(int|array $queryId): self
    {
        $this->queryId = $queryId;

        return $this;
    }

    public function toCsv(?Collection $models = null): array
    {
        return $this->extract(ExtractBuilder::FORMAT_CSV, $models);
    }

    public function toSql(?Collection $models = null): array
    {
        return $this->extract(ExtractBuilder::FORMAT_SQL, $models);
    }

    public function extract(string $format, ?Collection $models = null): array
    {
        $this->builder->createBuilder($format);

        $models = $models ? $this->validateCustomModel($models) : $this->query();

        // Flatten all models and their relationships
        $this->flattenRelation($models);

        // Build result
        $this->buildResult();

        return $this->results;
    }

    public function validateCustomModel(Collection $models): Collection
    {
        if (! is_subclass_of($models->first(), Model::class)) {
            throw new Exception('The provided model, parent must be an instance of Illuminate\Database\Eloquent\Model');
        }

        return $models;
    }

    protected function query(): mixed
    {
        if (is_null($this->option) && is_null($this->source)) {
            throw new Exception('Option or source are not set.');
        }

        // DTO Class
        $source = $this->source?->get()->toArray() ?? $this->option?->source()->toArray();

        $ids = is_int($this->queryId) ? [$this->queryId] : $this->queryId;

        $query = app($source['model'])
            ->setConnection($source['connection'])
            ->whereIn('id', $ids)
            ->with($source['relationships'] ?? []);

        return $query->get();
    }

    protected function flattenRelation(Collection $models): void
    {
        $models->each(function ($model) {
            $this->datas[] = $model;

            // Get all loaded relations
            $relations = $model->getRelations();

            foreach ($relations as $relationData) {
                if ($relationData instanceof Collection && $relationData->isNotEmpty()) {
                    // For collections (hasMany, belongsToMany)
                    $relationData->each(fn ($relation) => $this->datas[] = $relation);
                    // Recursively flatten nested relations
                    $this->flattenRelation($relationData);
                } elseif (is_object($relationData) && method_exists($relationData, 'getRelations')) {
                    // For single models (hasOne, belongsTo)
                    $this->datas[] = $relationData;
                    // Recursively flatten nested relations
                    $this->flattenRelation(collect([$relationData]));
                }
            }
        });
    }

    protected function buildResult(): void
    {
        collect($this->datas)->unique(function ($data) {
            return $data::class.$data['id'];
        })->each(function ($data) {
            $this->results[] = $this->builder
                ->setModel($data)
                ->build();
        });
    }
}
