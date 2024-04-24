<?php

namespace WP_Framework\Database\Table;

use WP_Framework\Database\SQL\Statement\Insert;
use WP_Framework\Database\SQL\Statement\Select;
use WP_Framework\Database\SQL\ThinSkinnedSyntaxCheck as SyntaxCheck;

/**
 * Class AbstractTable
 *
 * Represents an abstract database table.
 *
 * @package WP_Framework\Database\Table
 */
abstract class AbstractTable
{

    /** @var string The name of the primary key column */
    public string $id_column_name;

    /**
     * Constructor.
     *
     * @param string $name The name of the table in the database.
     * @throws \Error If the provided name is not a valid table name.
     */
    public function __construct(public string $name)
    {
        SyntaxCheck::is_table_name($name);
        $this->set_id_column_name();
    }

    /**
     * Sets the name of the primary key column.
     *
     * @return AbstractTable Instance of the current AbstractTable for method chaining.
     */
    abstract protected function set_id_column_name(): AbstractTable;

    /**
     * Returns the prefix for column names.
     *
     * @return string The column prefix.
     */
    abstract public function get_column_prefix(): string;

    /**
     * Constructs a SELECT statement for querying the table. Call 'execute' to get results.
     *
     * @param string $column The column(s) to select.
     * @param string ...$more_columns Additional columns to select.
     * @return Select An instance of the Select statement builder.
     */
    public function select(string $column = '*', string ...$more_columns): Select
    {
        return new Select($this, $column, ...$more_columns);
    }

    /**
     * Retrieves all rows from the table.
     *
     * @return array|null An array of rows or null if no rows are found.
     */
    public function get_rows(): ?array
    {
        $rows = $this
            ->select()
            ->execute();
        if ($rows) {
            return $rows;
        }
        return null;
    }

    /**
     * Retrieves rows from the table based on a search term across specified columns.
     *
     * @param string $search_term The search term to look for in the specified columns.
     * @param string ...$columns The columns to search within.
     * @return array|null An array of rows matching the search term, or null if no matches are found.
     */
    public function get_rows_by_search(string $search_term, string ...$columns): ?array
    {
        $first_column = array_shift($columns);
        $select_statement = $this
            ->select()
            ->where_like($first_column, "%{$search_term}%");

        foreach ($columns as $column) {
            $select_statement
                ->or()
                ->where_like($column, "%{$search_term}%");
        }

        $rows = $select_statement->execute();
        return $rows ?? null;
    }

    /**
     * Retrieves a single row from the table based on its ID.
     *
     * @param int $id The ID of the row to retrieve.
     * @return array|null The retrieved row or null if the row is not found.
     */
    public function get_row(int $id): ?array
    {
        $rows = $this
            ->select()
            ->where_equals($this->id_column_name, $id)
            ->limit(1)
            ->execute();
        return $rows ?? null;
    }

    /**
     * Retrieves a specific field from a row in the table based on its ID.
     *
     * @param string $column The name of the column to retrieve.
     * @param int $id The ID of the row.
     * @return null|string|int The value of the field or null if the field is not found.
     */
    public function get_field(string $column, int $id): null|string|int
    {
        $rows = $this
            ->select($column)
            ->where_equals($this->id_column_name, $id)
            ->limit(1)
            ->execute();
        return $rows[0][$column] ?? null;
    }

    /**
     * When not passing the column-names, you need to use the values-method, and add as many values as columns are in the table. , make sure the order of the values is in the same order as the columns in the table.
     *
     * @param string|AbstractTable $table The name of the table or an instance of AbstractTable
     * @throws \InvalidArgumentException If the provided table or column names are invalid
     */
    public function insert(string ...$column_names): Insert
    {
        return new Insert($this, ...$column_names);
    }

    public function add_row(array $column_value_pairs): static
    {
        $colum_names = array_keys($column_value_pairs);
        $this->insert(...$colum_names)->values(...$column_value_pairs)->execute();
        return $this;
    }
}
