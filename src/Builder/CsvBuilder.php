<?php

namespace NaimSolong\DataExtractor\Builder;

class CsvBuilder extends BaseBuilder
{
    /**
     * Build the data as CSV format.
     */
    public function build(): string
    {
        $csv = '';

        // Add header row
        $csv .= implode(',', $this->columns)."\n";

        $csvRow = [];

        // Add data rows
        foreach ($this->columns as $column) {
            if (! array_key_exists($column, $this->data)) {
                $csvRow[] = "'*****'";

                continue;
            }

            $value = $this->data[$column] ?? '';
            // Escape CSV values
            $csvRow[] = '"'.str_replace('"', '""', $value).'"';
        }
        $csv .= implode(',', $csvRow)."\n";

        return $csv;
    }
}
