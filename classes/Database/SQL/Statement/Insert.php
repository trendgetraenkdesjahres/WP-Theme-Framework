<?php

namespace WP_Framework\Database\SQL\Statement;

use WP_Framework\Database\Database;
use WP_Framework\Database\Table\AbstractTable;

/**
 * Class Insert
 *
 * Represents a INSETRT statement builder.
 *
 * @package WP_Framework\Database\Statement
 */
class Insert extends AbstractStatement
{
    use Clause\ValuesClauseTrait;
    /**
     * Insert constructor.
     *
     * Constructs a Insert statement. Convertible to a string.
     *
     * When not passing the column-names, you need to use the values-method, and add as many values as columns are in the table. , make sure the order of the values is in the same order as the columns in the table.
     *
     * @param string|AbstractTable $table The name of the table or an instance of AbstractTable
     * @throws \InvalidArgumentException If the provided table or column names are invalid
     */
    public function __construct(string|AbstractTable $table, string ...$columns)
    {
        $this->set_table($table);
        $this->set_columns($columns);
    }

    /**
     * Executes the constructed INSERT statement and returns the resulting rows.
     *
     * @return array An array with the element 'LAST_INSERT_ID()'.
     * @throws \Error If the VALUES clause is not completed.
     */
    public function execute(): array
    {
        if (!$this->values_clause_completed) {
            throw new \Error("Statement is not completed.");
        }

        $columns = '';
        if ($this->columns) {
            $columns = "({$this->columns})";
        }
        Database::query("INSERT INTO {$this->table} {$columns} {$this->get_values_clause()};");
        return Database::get_result("SELECT LAST_INSERT_ID()")[0];
    }
}
