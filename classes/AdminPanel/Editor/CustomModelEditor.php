<?php

namespace WP_Framework\AdminPanel\Editor;

use WP_Framework\AdminPanel\Editor\Editor;
use WP_Framework\Debug\Debug;
use WP_Framework\Model\CustomModel;

class CustomModelEditor extends Editor
{
    public function __construct(CustomModel $model, ?object $object = null)
    {
        parent::__construct(
            name: $model->sanitized_name,
            action: 'post.php',
            object: $object,
            meta_sidebar: ($model->meta === false ? false : true)
        );
        $this->add_property(...$model->get_properties());
    }
}
