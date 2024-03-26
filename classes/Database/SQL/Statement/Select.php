<?php

namespace WP_Framework\Database\SQL\Statement;

use WP_Framework\Database\Database;
use WP_Framework\Database\Table\AbstractTable;
use WP_Framework\Debug\Debug;

/**
 * Class Select
 *
 * Represents a SELECT statement builder.
 *
 * @package WP_Framework\Database\Statement
 */
class Select extends AbstractStatement
{
    use Clause\WhereClauseTrait;

    /** @var string The LIMIT clause */
    private string $limit = '';

    /** @var null|array The result, which will be filled be execute and __deconstruct() */
    private ?array $result = null;

    /**
     * Select constructor.
     *
     * Constructs a Select statement. Convertible to a string.
     *
     * @param string|AbstractTable $table The name of the table or an instance of AbstractTable
     * @param string $column The first column to select (default is '*')
     * @param string ...$more_columns Additional columns to select
     * @throws \InvalidArgumentException If the provided table or column names are invalid
     */
    public function __construct(string|AbstractTable $table, string $column = '*', string ...$more_columns)
    {
        $this->set_table($table);
        if ($column === '*') {
            $this->columns = '*';
        } else {
            $this->set_columns(array_merge([$column], $more_columns));
        }
    }

    /**
     * Destructor method to execute the SELECT statement if the result array is set.
     */
    public function __deconstruct()
    {
        if (is_array($this->result)) {
            $this->execute();
        }
    }

    /**
     * Sets the LIMIT clause for the SELECT statement.
     *
     * @param int $limit The maximum number of rows to return
     * @return Select Instance of the current Select for method chaining.
     */
    public function limit(int $limit): self
    {
        $this->limit = "LIMIT $limit";
        return $this;
    }

    /**
     * Executes the constructed SELECT statement and returns the resulting rows.
     *
     * @return array An array of rows resulting from the executed SELECT statement.
     * @throws \Error If the WHERE clause is not completed.
     */
    public function execute(): array
    {
        if (!$this->where_clause_completed) {
            throw new \Error("Statement is not completed.");
        }
        $this->result =  Database::get_result("SELECT {$this->columns} FROM {$this->table} {$this->get_where_clause()} {$this->limit};");
        return $this->result;
    }

    /**
     * Sets the reference to the result array for later population upon destruction.
     *
     * Using this method will cause the magic destructor to execute a database query.
     *
     * @param array $array The reference to the result array to be populated.
     * @return Select An instance of the current Select object for method chaining.
     */
    public function set_result_array(array &$array): self
    {
        $this->result = &$array;
        return $this;
    }
}
