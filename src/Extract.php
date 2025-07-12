<?php

namespace NaimSolong\DataExtractor;

use Exception;
use NaimSolong\DataExtractor\Builder\ExtractBuilder;

class Extract
{
    protected int $queryId;

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

    public function queryId(int $queryId): self
    {
        $this->queryId = $queryId;

        return $this;
    }

    public function query(): mixed
    {
        if (is_null($this->option) && is_null($this->source)) {
            throw new Exception('Option or source are not set.');
        }

        // DTO Class
        $source = $this->source?->get()->toArray() ?? $this->option?->source()->toArray();

        $query = app($source['model'])
            ->setConnection($source['connection'])
            ->with($source['relationships'] ?? []);

        return $query->findOrFail($this->queryId);
    }

    public function toCsv(): string
    {
        return $this->extract(ExtractBuilder::FORMAT_CSV);
    }

    public function toSql(): string
    {
        return $this->extract(ExtractBuilder::FORMAT_SQL);
    }

    public function extract(string $format): string
    {
        $this->builder->createBuilder($format);

        $data = $this->query();

        return $this->builder
            ->setModel($data)
            ->build();
    }
}
