<?php

namespace NaimSolong\DataExtractor\Builder;

class CsvBuilder extends BaseBuilder
{
    /**
     * Build the data as CSV format.
     *
     * @return string
     */
    public function build(): string
    {
        $csv = '';
        
        // Add header row
        $csv .= implode(',', $this->columns) . "\n";
        
        // Add data rows
        foreach ($this->data as $row) {
            $csvRow = [];
            foreach ($this->columns as $column) {
                $value = $row[$column] ?? '';
                // Escape CSV values
                $csvRow[] = '"' . str_replace('"', '""', $value) . '"';
            }
            $csv .= implode(',', $csvRow) . "\n";
        }
        
        return $csv;
    }
}