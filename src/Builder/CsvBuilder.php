<?php

namespace NaimSolong\DataExtractor\Builder;

use DateTime;

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
            if (is_array($value)) {
                $csvRow[] = "'".json_encode($value, JSON_UNESCAPED_UNICODE)."'";
            } elseif (is_null($value)) {
                $csvRow[] = 'NULL';
            } elseif (is_numeric($value)) {
                $csvRow[] = $value;
            } elseif (is_bool($value)) {
                $csvRow[] = ($value ? "'1'" : "'0'");
            } elseif ($value instanceof DateTime) {
                $csvRow[] = "'".$value->format('Y-m-d H:i:s')."'";
            } else {
                $csvRow[] = '"'.str_replace('"', '""', $value).'"';
            }
        }
        $csv .= implode(',', $csvRow)."\n";

        return $csv;
    }
}
