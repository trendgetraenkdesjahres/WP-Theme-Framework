<?php

namespace WP_Framework\Database\SQL\Statement;


use WP_Framework\Database\SQL\ThinSkinnedSyntaxCheck as SyntaxCheck;
use WP_Framework\Database\Database;
use WP_Framework\Database\Table\AbstractTable;

/**
 * Class Update
 *
 * Represents a UPDATE statement builder.
 *
 * @package WP_Framework\Database\Statement
 */
class Update extends AbstractStatement
{
    use Clause\WhereClauseTrait;

    private string $set = "SET ";

    /**
     * Insert constructor.
     *
     * Constructs a Update statement. Convertible to a string.
     *
     * @param string|AbstractTable $table The name of the table or an instance of AbstractTable
     * @throws \InvalidArgumentException If the provided table or column names are invalid
     */
    public function __construct(string|AbstractTable $table)
    {
        $this->set_table($table);
    }

    public function set($column, $value): self
    {
        SyntaxCheck::is_field_name($column);
        SyntaxCheck::is_safe_value($value);

        if ($this->set !== "SET ") {
            $this->set .= ', ';
        }
        $this->set .= "{$column} = '{$value}'";
        return $this;
    }

    /**
     * Executes the constructed UPDATE statement and returns the resulting rows.
     *
     * @return array An array of rows resulting from the executed UPDATE statement.
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
