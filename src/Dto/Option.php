<?php

namespace NaimSolong\DataExtractor\Dto;

use NaimSolong\DataExtractor\Builder\ExtractBuilder;

readonly class Option
{
    public function __construct(
        public string $name,
        public string $description,
        public string $format,
        public Source $source,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? '',
            format: $data['format'] ?? ExtractBuilder::DEFAULT_FORMAT,
            source: $data['source'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'format' => $this->format,
            'source' => $this->source->toArray(),
        ];
    }
}
