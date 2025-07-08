<?php

namespace NaimSolong\DataExtractor\Builder;

class SqlBuilder extends BaseBuilder
{
    /**
     * Build the data as SQL INSERT statements.
     */
    public function build(): string
    {
        $sql = '';

        foreach ($this->data as $row) {
            $values = [];
            foreach ($this->columns as $column) {
                $value = $row[$column] ?? null;

                if (is_array($value)) {
                    $values[] = "'".json_encode($value, JSON_UNESCAPED_UNICODE)."'";
                } elseif (is_null($value)) {
                    $values[] = 'NULL';
                } elseif (is_numeric($value)) {
                    $values[] = $value;
                } elseif (is_bool($value)) {
                    $values[] = ($value ? "'1'" : "'0'");
                } else {
                    $values[] = "'".addslashes($value)."'";
                }
            }

            $sql .= "INSERT INTO {$this->schemaName} (".implode(', ', $this->columns).') VALUES ('.implode(', ', $values).");\n";
        }

        return $sql;
    }
}
