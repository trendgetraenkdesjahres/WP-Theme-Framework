<?php

namespace WP_Framework\Database\Table;

use WP_Framework\Database\Database;
use WP_Framework\Database\SQLSyntax;

abstract class AbstractTable
{
    public string $id_column_name;

    abstract protected function set_id_column_name(string $name): AbstractTable;

    /**
     * __construct
     *
     * @param  mixed $name the name of the table in the database
     */
    public function __construct(public string $name)
    {
        if (!SQLSyntax::is_field_name($name)) {
            throw new \Error("{$name} is not a valid table name.");
        }
        $this->set_id_column_name($name);
    }

    public function get_row(int $id): ?array
    {
        # TODO implement error checking
        $rows = $this->select(
            where_clause: "{$this->id_column_name} = {$id}"
        );
        if ($rows) {
            return $rows[0];
        }
        return null;
    }

    public function get_field(string $column)
    {
    }

    private function select(string $columns = "*", string $where_clause = '', int $limit = 1): array
    {
        return Database::get_result("SELECT {$columns} FROM {$this->name} WHERE {$where_clause} LIMIT {$limit}");
    }
}
