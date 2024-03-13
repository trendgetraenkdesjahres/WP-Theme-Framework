<?php

namespace WP_Framework\Database\Table;

use WP_Framework\Database\Database;
use WP_Framework\Database\QueryResult;
use WP_Framework\Database\SQLSyntax;

abstract class AbstractTable
{
    protected string $id_column_name;

    abstract protected function set_id_column_name(string $name): AbstractTable;

    public function __construct(public string $name)
    {
        if (!SQLSyntax::is_field_name($name)) {
            throw new \Error("{$name} is not a valid table name.");
        }
        $this->set_id_column_name($name);
    }

    public function get_row(int $id): QueryResult
    {
        $row = wp_cache_get(
            key: $id,
            group: $this->name,
            found: $success_on_cache = false
        );
        if ($success_on_cache) {
            return $row;
        }
        $row = $this->select(
            where_clause: "{$this->id_column_name} = {$id}"
        );
        wp_cache_add(
            key: $id,
            data: $row,
            group: $this->name,
        );
        return $row;
    }

    private function select(string $columns = "*", string $where_clause = '', int $limit = 1): QueryResult
    {
        return Database::get_result("SELECT {$columns} FROM {$this->name} WHERE {$where_clause} LIMIT {$limit}");
    }
}
