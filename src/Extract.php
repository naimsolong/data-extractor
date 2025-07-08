<?php

namespace NaimSolong\DataExtractor;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use NaimSolong\DataExtractor\Builder\ExtractBuilder;

class Extract
{
    protected int $queryId;

    protected InstructionsResolver $instructions;

    protected ExtractBuilder $builder;

    public function __construct()
    {
        $this->builder = new ExtractBuilder;
        $this->instructions = new InstructionsResolver;
    }

    public function instruction(int|string $value): self
    {
        $this->instructions->set($value);

        return $this;
    }

    public function queryId(int $queryId): self
    {
        $this->queryId = $queryId;

        return $this;
    }

    public function query(): mixed
    {
        $source = $this->instructions->source()->toArray();

        $query = app($source['model'])
            ->setConnection($source['connection'])
            ->with($source['relationships'] ?? []);

        return $query->findOrFail($this->queryId);
    }

    public function extract(string $format): string
    {
        $this->builder->createBuilder($format);

        $data = $this->query();

        return $this->builder
            ->setModel($data)
            ->build();
    }

    public function toCsv(): string
    {
        return $this->extract(ExtractBuilder::FORMAT_CSV);
    }

    public function toSql(): string
    {
        return $this->extract(ExtractBuilder::FORMAT_SQL);
    }
}
