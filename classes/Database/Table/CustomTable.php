<?php

namespace WP_Framework\Database\Table;

class CustomTable extends AbstractTable
{
    protected function set_id_column_name(string $name): CustomTable
    {
        if (str_ends_with('meta', $name)) {
            $this->id_column_name = 'meta_id';
        } else {
            $this->id_column_name = substr($name, 3, -1) . "_id";
        }
        return $this;
    }
}
