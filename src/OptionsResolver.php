<?php

namespace NaimSolong\DataExtractor;

use InvalidArgumentException;
use NaimSolong\DataExtractor\Dto\Option;
use NaimSolong\DataExtractor\Dto\Source;

class OptionsResolver
{
    protected array $options = [];

    protected Option $option;

    public function __construct()
    {
        $this->resolveInstance();
    }

    protected function resolveInstance(): void
    {
        $configOptions = config('data-extractor.options', []);
        $configSource = config('data-extractor.source', []);

        foreach ($configOptions as $option) {
            $source = (array_key_exists($option['source'], $configSource)) ? $configSource[$option['source']] : [];

            $this->options[] = Option::fromArray([
                'name' => $option['name'],
                'description' => $option['description'],
                'format' => $option['format'],
                'source' => Source::fromArray($source),
            ]);
        }
    }

    public function set(int|string $value): self
    {
        $length = count($this->options);

        if (is_int($value) && ($value >= 0 && $value < $length)) {
            $this->option = $this->options[$value];

            return $this;
        }

        if (is_string($value)) {
            $filteredOptions = array_filter($this->options, function ($option) use ($value) {
                return $option->name === $value;
            });

            if (count($filteredOptions) > 0) {
                $this->option = $filteredOptions[0];

                return $this;
            }
        }

        throw new InvalidArgumentException("Invalid option value: {$value}");
    }

    public function get(): Option
    {
        return $this->option;
    }

    public function source(): Source
    {
        return $this->option->source;
    }
}
