<?php

namespace NaimSolong\DataExtractor;

use InvalidArgumentException;
use NaimSolong\DataExtractor\Dto\Export;
use NaimSolong\DataExtractor\Dto\Instruction;
use NaimSolong\DataExtractor\Dto\Source;

class InstructionsResolver
{
    protected array $instructions = [];

    protected Instruction $instruction;

    public function __construct()
    {
        $this->resolveInstance();
    }

    protected function resolveInstance(): void
    {
        $configInstructions = config('data-extractor.instructions', []);
        $configSource = config('data-extractor.source', []);
        $configExport = config('data-extractor.export', []);

        foreach ($configInstructions as $instruction) {
            $source = (array_key_exists($instruction['source'], $configSource)) ? $configSource[$instruction['source']] : [];
            $export = (array_key_exists($instruction['export'], $configExport)) ? $configExport[$instruction['export']] : [];

            $this->instructions[] = Instruction::fromArray([
                'name' => $instruction['name'],
                'description' => $instruction['description'],
                'format' => $instruction['format'],
                'source' => Source::fromArray($source),
                'export' => Export::fromArray($export),
            ]);
        }
    }

    public function set(int|string $value): self
    {
        $length = count($this->instructions);

        if (is_int($value) && ($value >= 0 && $value < $length)) {
            $this->instruction = $this->instructions[$value];

            return $this;
        }

        if (is_string($value)) {
            $filteredInstructions = array_filter($this->instructions, function ($instruction) use ($value) {
                return $instruction->name === $value;
            });

            if (count($filteredInstructions) > 0) {
                $this->instruction = $filteredInstructions[0];

                return $this;
            }
        }

        throw new InvalidArgumentException("Invalid instruction value: {$value}");
    }

    public function get(): Instruction
    {
        return $this->instruction;
    }

    public function source(): Source
    {
        return $this->instruction->source;
    }

    public function export(): Export
    {
        return $this->instruction->export;
    }
}
