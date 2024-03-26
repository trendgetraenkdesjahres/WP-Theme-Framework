<?php

namespace WP_Framework\Admin\Screen;

use WP_Framework\Admin\Screen\Table\ModelTable;
use WP_Framework\Admin\Screen\Editor\ModelEditor;
use WP_Framework\Model\CustomModel;

class ModelPanel extends Panel
{
    public function __construct(CustomModel $model)
    {
        add_action($this->register_hook, function () use ($model) {
            $this->table = new ModelTable($model);
            $this->editor = new ModelEditor($model);
        });

        $capability = $model->get_capability_attribute("edit_{$model->name}s", false);

        parent::__construct(
            required_capabilty: $capability,
            name: $model->singular_name,
            plural_name: $model->plural_name,
        );
    }
}
