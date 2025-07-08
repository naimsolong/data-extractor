<?php

namespace NaimSolong\DataExtractor\Dto;

use ExtractBuilder;

readonly class Instruction
{
    public function __construct(
        public string $name,
        public string $description,
        public string $format,
        public Source $source,
        public Export $export,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? '',
            format: $data['format'] ?? ExtractBuilder::DEFAULT_FORMAT,
            source: $data['source'],
            export: $data['export'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'format' => $this->format,
            'source' => $this->source->toArray(),
            'export' => $this->export->toArray(),
        ];
    }
}
