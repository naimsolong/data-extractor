<?php

namespace NaimSolong\DataExtractor;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use NaimSolong\DataExtractor\Builder\ExtractBuilder;

class Extract {
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

    public function query(string $format): string
    {
        $this->builder->createBuilder($format);

        $source = $this->instructions->source()->toArray();

        $query = app($source['model'])
            ->setConnection($source['connection'])
            ->with($source['relationships'] ?? [])
            ->where('id', $this->queryId);

        if (! $query->exists()) {
            throw new ModelNotFoundException("No record found with ID {$this->queryId} in the {$source['model']} model.");
        }

        $data = $query->first();

        ray($data);
        
        return $this->builder
            ->setModel($data)
            ->build();
    }

    public function toCsv(): string
    {
        return $this->query(ExtractBuilder::FORMAT_CSV);
    }

    public function toSql(): string
    {
        return $this->query(ExtractBuilder::FORMAT_SQL);
    }
}
