<?php

namespace WP_Framework\Database\SQL\Statement;

use WP_Framework\Database\SQL\ThinSkinnedSyntaxCheck as SyntaxCheck;
use WP_Framework\Database\Table\AbstractTable;

/**
 * Class Select
 *
 * Represents a SELECT statement builder.
 *
 * @package WP_Framework\Database\Statement
 */
abstract class AbstractStatement
{
    /** @var string The name of the table */
    protected string $table;

    /** @var string The specified columns */
    protected string $columns = '';

    /**
     * Executes the constructed statement and returns the resulting rows.
     *
     * @return array An array of rows resulting from the executed statement.
     * @throws \Error If something is not completed.
     */
    abstract public function execute(): array;

    /**
     * Sets the table for the SELECT statement.
     *
     * @param string|AbstractTable $table The name of the table or an instance of AbstractTable
     * @return Select Instance of the current Select for method chaining.
     * @throws \InvalidArgumentException If the provided table name is invalid
     */
    protected function set_table(string|AbstractTable $table): self
    {
        if (!is_string($table)) {
            $table = $table->name;
        }
        SyntaxCheck::is_table_name($table);
        $this->table = $table;
        return $this;
    }

    /**
     * Sets the columns to be selected in the statement.
     *
     * @param array $columns An array of column names
     * @return AbstractStatement Instance of the current statement for method chaining.
     * @throws \InvalidArgumentException If the provided column names are invalid
     */
    protected function set_columns($columns): self
    {
        foreach ($columns as $column) {
            SyntaxCheck::is_field_name($column);
            $this->columns .= "{$column}, ";
        }
        $this->columns = rtrim($this->columns, ', ');
        return $this;
    }
}
