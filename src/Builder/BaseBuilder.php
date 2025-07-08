<?php

namespace NaimSolong\DataExtractor\Builder;

abstract class BaseBuilder
{
    /**
     * The name of the schema.
     *
     * @var string
     */
    protected string $schemaName;

    /**
     * The columns available on schema.
     *
     * @var array
     */
    protected array $columns;

    /**
     * The data to be processed.
     *
     * @var array
     */
    protected array $data;

    /**
     * Get the schema name.
     *
     * @return string
     */
    public function getSchemaName(): string
    {
        return $this->schemaName;
    }

    /**
     * Set the schema name.
     *
     * @param string $schemaName
     * @return self
     */
    public function setSchemaName(string $schemaName): self
    {
        $this->schemaName = $schemaName;

        return $this;
    }

    /**
     * Get the columns.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Set the columns.
     *
     * @param array $columns
     * @return self
     */
    public function setColumns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Get the data.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set the data.
     *
     * @param array $data
     * @return self
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