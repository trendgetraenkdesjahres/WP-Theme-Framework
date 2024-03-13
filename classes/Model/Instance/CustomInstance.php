<?php

namespace WP_Framework\Model\Instance;

use WP_Framework\Model\CustomModel;

/**
 * DataModel is the class to implement custom models.
 *
 * @package WP_Framework\Model
 */
class CustomInstance
{
    public function __construct(CustomModel $model, array $properties)
    {
        foreach ($model->get_properties() as $model_property) {
            $this->{$model_property->key} = $properties[$model_property->key];
        }
    }
}
