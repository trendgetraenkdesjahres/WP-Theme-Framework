<?php

namespace WP_Framework\Database\SQL\Statement;

use WP_Framework\Database\Database;
use WP_Framework\Database\Table\AbstractTable;

/**
 * Class Delete
 *
 * Represents a DELETE statement builder.
 *
 * @package WP_Framework\Database\Statement
 */
class Delete extends AbstractStatement
{
    use Clause\WhereClauseTrait;
    /**
     * Insert constructor.
     *
     * Constructs a Delete statement. Convertible to a string.
     *
     * @param string|AbstractTable $table The name of the table or an instance of AbstractTable
     * @throws \InvalidArgumentException If the provided table or column names are invalid
     */
    public function __construct(string|AbstractTable $table)
    {
        $this->set_table($table);
    }

    /**
     * Executes the constructed DELETE statement and returns the resulting rows.
     *
     * @return array An array of rows resulting from the executed DELETE statement.
     * @throws \Error If the WHERE clause is not completed.
     */
    public function execute(): array
    {
        if (!$this->where_clause_completed) {
            throw new \Error("Statement is not completed.");
        }
        return Database::get_result("DELETE FROM {$this->table} {$this->get_where_clause()};");
    }
}
