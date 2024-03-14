<?php

namespace WP_Framework\Model\Instance;

use WP_Framework\Database\Database;

class CustomInstanceWithMeta extends CustomInstance{
    public function get_meta(string $key)
    {
        Database::get_table($this->table_name)->get_row()
        # try cache
        # try db
        # try default
    }
}