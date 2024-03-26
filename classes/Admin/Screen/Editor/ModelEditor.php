<?php

namespace WP_Framework\Admin\Screen\Editor;

use WP_Framework\Admin\Screen\Editor\Editor;
use WP_Framework\Debug\Debug;
use WP_Framework\Model\CustomModel;
use WP_Framework\Model\Instance\CustomInstance;

class ModelEditor extends Editor
{
    public function __construct(CustomModel $model, ?CustomInstance $instance = null)
    {
        parent::__construct(
            name: $model->name,
            action: 'post.php',
            instance: $instance,
            meta_sidebar: ($model->meta === false ? false : true)
        );
        $this->add_property_form(...$model->get_properties());
    }
}
