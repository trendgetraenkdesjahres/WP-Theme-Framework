<?php

namespace WP_Framework\Admin\Panel;

use WP_Framework\Admin\Panel\Table\ModelTable;
use WP_Framework\Admin\Panel\Editor\ModelEditor;
use WP_Framework\Model\CustomModel;

class ModelPanel extends AbstractPanel
{
    private CustomModel $model;

    public function __construct(CustomModel $model)
    {
        $this->model = $model;
        $capability = $model->get_capability_attribute("edit_{$model->name}s", false);

        parent::__construct(
            required_capabilty: $capability,
            name: $model->singular_name,
            plural_name: $model->plural_name,
        );
    }

    public function get_table_screen(): string
    {
        return (string) new ModelTable($this->model);
    }

    public function get_create_new_screen(): string
    {
        return (string) new ModelEditor($this->model);

    }
}
