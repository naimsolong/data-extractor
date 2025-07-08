<?php

namespace NaimSolong\DataExtractor;

use Exception;
use NaimSolong\DataExtractor\Builder\ExtractBuilder;

class Extract
{
    protected int $queryId;

    protected ?InstructionsResolver $instruction = null;

    protected ?SourcesResolver $source = null;

    protected ExtractBuilder $builder;

    public function __construct()
    {
        $this->builder = new ExtractBuilder;
    }

    public function instruction(int|string $value): self
    {
        $this->instruction = (new InstructionsResolver)->set($value);

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
        if (is_null($this->instruction) && is_null($this->source)) {
            throw new Exception('Instruction or source are not set.');
        }

        $source = $this->source?->get()->toArray() ?? $this->instruction?->source()->toArray();

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
