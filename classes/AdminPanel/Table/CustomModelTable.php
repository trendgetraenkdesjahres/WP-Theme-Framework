<?php

namespace WP_Framework\AdminPanel\Table;

use WP_Framework\Model\CustomModel;

class CustomModelTable extends AbstractTable
{
    public function __construct(CustomModel $model)
    {
        # call basic constructor
        parent::__construct(
            item_name: $model->name,
            items_name: "{$model->name}s",
            ajax: false
        );
        $this->bind_to_database_table($model->get_custom_model_table_name());

        # create columns
        foreach ($model->properties as $property) {
            $this->add_column(
                name: $property->key,
                title: $property->singular_name,
                sortable: $property->is_indexable,
                visible: true
            );
        }
    }
}
