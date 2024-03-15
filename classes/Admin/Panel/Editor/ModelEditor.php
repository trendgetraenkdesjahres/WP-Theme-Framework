<?php

namespace WP_Framework\Admin\Panel\Editor;

use WP_Framework\Admin\Panel\Editor\Editor;
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
            object: $instance,
            meta_sidebar: ($model->meta === false ? false : true)
        );
        $this->add_property(...$model->get_properties());
    }
}
