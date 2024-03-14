<?php

namespace WP_Framework\Database\Table;

class BuildinTable extends AbstractTable
{
    protected function set_id_column_name(string $name): BuildinTable
    {
        if (str_ends_with('usermeta', $name)) {
            $this->id_column_name = 'umeta_id';
        } elseif (str_ends_with('meta', $name)) {
            $this->id_column_name = 'meta_id';
        } elseif ($name == 'wp_posts' || $name == 'wp_users') {
            $this->id_column_name = 'ID';
        } elseif ($name == 'wp_term_relationships') {
            $this->id_column_name = 'object_id';
        } else {
            $this->id_column_name = substr($name, 3, -1) . "_id";
        }
        return $this;
    }
}
