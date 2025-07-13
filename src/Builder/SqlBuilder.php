<?php

namespace NaimSolong\DataExtractor\Builder;

use Carbon\Carbon;
use DateTime;

class SqlBuilder extends BaseBuilder
{
    /**
     * Build the data as SQL INSERT statements.
     */
    public function build(): string
    {
        $sql = '';

        $rowValues = [];
        $values = [];

        foreach ($this->columns as $column) {
            if (! array_key_exists($column, $this->data)) {
                $values[] = "'*****'";
                
                continue;
            }

            $value = $this->data[$column];

            if (is_array($value)) {
                $values[] = "'".json_encode($value, JSON_UNESCAPED_UNICODE)."'";
            } elseif (is_null($value)) {
                $values[] = 'NULL';
            } elseif (is_numeric($value)) {
                $values[] = $value;
            } elseif (is_bool($value)) {
                $values[] = ($value ? "'1'" : "'0'");
            } elseif ($value instanceof DateTime) {
                $values[] = "'".$value->format('Y-m-d')."'";
            } else {
                $values[] = "'".addslashes($value)."'";
            }
        }

        $rowValues[] = $values;
        $values = [];

        $arrayValues = [];
        foreach($rowValues as $row) {
            $arrayValues[] = "(".implode(', ', $row).")";
        }

        $sql .= "INSERT INTO {$this->schemaName} (".implode(', ', $this->columns)."') VALUES ".implode(', ', $arrayValues).";";

        return $sql;
    }
}
