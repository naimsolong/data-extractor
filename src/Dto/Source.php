<?php

namespace NaimSolong\DataExtractor\Dto;

use Exception;
use Illuminate\Database\Eloquent\Model;

readonly class Source
{
    public function __construct(
        public string $model,
        public string $connection,
        public array $relationships = [],
    ) {}

    public static function fromArray(array $data): self
    {
        if (! is_subclass_of($data['model'] ?? null, Model::class)) {
            throw new Exception('The provided model, parent must be an instance of Illuminate\Database\Eloquent\Model');
        }

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
