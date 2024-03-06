<?php

namespace WP_Framework\AdminPanel;

use WP_Framework\AdminPanel\Table\ModelTable;

class ModelPanel extends AbstractPanel
{
    public string  $required_capabilty = '';
    protected function get_body(): string
    {
        return '';
    }
}
