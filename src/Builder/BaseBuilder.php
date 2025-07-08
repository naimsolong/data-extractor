<?php

namespace NaimSolong\DataExtractor\Builder;

abstract class BaseBuilder
{
    /**
     * The name of the schema.
     */
    protected string $schemaName;

    /**
     * The columns available on schema.
     */
    protected array $columns;

    /**
     * The data to be processed.
     */
    protected array $data;

    /**
     * Get the schema name.
     */
    public function getSchemaName(): string
    {
        return $this->schemaName;
    }

    /**
     * Set the schema name.
     */
    public function setSchemaName(string $schemaName): self
    {
        $this->schemaName = $schemaName;

        return $this;
    }

    /**
     * Get the columns.
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Set the columns.
     */
    public function setColumns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Get the data.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set the data.
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Build the data.
     *
     * @return mixed
     */
    abstract public function build();
}
