<?php

namespace NaimSolong\DataExtractor;

use InvalidArgumentException;
use NaimSolong\DataExtractor\Dto\Source;

class SourcesResolver
{
    protected array $sources = [];

    protected Source $source;

    public function __construct()
    {
        $this->sources = config('data-extractor.source', []);
    }

    public function set(string $value): self
    {
        if (array_key_exists($value, $this->sources)) {
            $this->source = Source::fromArray($this->sources[$value]);

            return $this;
        }

        throw new InvalidArgumentException("Invalid source value: {$value}");
    }

    public function get(): Source
    {
        if (empty($this->source)) {
            throw new InvalidArgumentException('Source has not been set. Please call set() method with a valid source key.');
        }

        return $this->source;
    }
}
