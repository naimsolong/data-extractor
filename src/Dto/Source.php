<?php

namespace NaimSolong\DataExtractor\Dto;

readonly class Source
{
    public function __construct(
        public string $model,
        public string $connection,
        public array $relationships = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            model: $data['model'],
            connection: $data['connection'] ?? config('database.default'),
            relationships: $data['relationships'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'model' => $this->model,
            'connection' => $this->connection,
            'relationships' => $this->relationships,
        ];
    }
}
