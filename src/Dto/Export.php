<?php

namespace NaimSolong\DataExtractor\Dto;

use InvalidArgumentException;

readonly class Export
{
    public function __construct(
        public string $file_name,
        public string $file_path,
        public string $disk,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            file_name: $data['file_name'] ?? 'export',
            file_path: $data['file_path'] ?? 'data-extractor',
            disk: $data['disk'] ?? 'local',
        );
    }

    public function toArray(): array
    {
        return [
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'disk' => $this->disk,
        ];
    }
}