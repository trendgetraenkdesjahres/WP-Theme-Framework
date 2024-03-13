<?php

namespace WP_Framework\Database\Table;

class ThirdPartyTable extends AbstractTable
{
    protected function set_id_column_name(string $name): ThirdPartyTable
    {
        return $this;
    }
}
