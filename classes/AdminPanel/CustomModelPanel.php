<?php

namespace WP_Framework\AdminPanel;

use WP_Framework\AdminPanel\Editor\CustomModelEditor;
use WP_Framework\AdminPanel\Table\CustomModelTable;
use WP_Framework\Model\CustomModel;

class CustomModelPanel extends AbstractPanel
{
    public string $required_capabilty = 'edit_themes';
    public CustomModel $model;

    public function __construct(CustomModel $model)
    {
        $this->model = $model;
        parent::__construct(
            singular_name: $model->name,
            plural_name: $model->plural_name
        );
    }

    protected function get_body(): string
    {
        ob_start();
        $table = new CustomModelTable($this->model);
        $table->prepare_items();
        $table->display();
        return ob_get_clean();
    }

    protected function get_editor_body($object_id): string
    {
        ob_start();
        echo  new CustomModelEditor($this->model, $object_id);
        return ob_get_clean();
    }
}
