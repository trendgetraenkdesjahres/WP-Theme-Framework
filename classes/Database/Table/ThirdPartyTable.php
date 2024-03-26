<?php

namespace WP_Framework\Database\Table;

class ThirdPartyTable extends AbstractTable
{
    protected function set_id_column_name(): ThirdPartyTable
    {
        return $this;
    }

    public function get_column_prefix(): string
    {
        return '';
    }
}
