<?php

namespace WP_Framework\Database\Table;

use WP_Framework\Database\Database;

class CustomTable extends AbstractTable
{
    protected function set_id_column_name(): CustomTable
    {
        $name = $this->name;
        if (str_ends_with('meta', $name)) {
            $this->id_column_name = 'meta_id';
        } else {
            $this->id_column_name = substr($name, 3, -1) . "_id";
        }
        return $this;
    }

    public function get_column_prefix(): string
    {
        if (str_ends_with('meta', $this->name)) {
            # will break if used on meta_table.
            throw new \Error('this case is not implemented yet.');
        }
        # removes the '$prefix_', the 's' at the end and appends an '_'.
        return preg_replace("/^" . Database::$table_prefix . "_|s$/", '', $this->name) . '_';
    }
}
