<?php

namespace WP_Framework\Admin\Screen\Table;

use WP_Framework\Admin\Screen\ScreenOption\ScreenOption;
use WP_Framework\Model\CustomModel;

class ModelTable extends Table
{
    /**
     * Constructs a ModelTable instance.
     *
     * @param CustomModel $model The custom model associated with the table.
     */
    public function __construct(CustomModel $model)
    {
        # call basic constructor
        parent::__construct(
            item_name: $model->name,
            items_name: "{$model->name}s",
            ajax: false
        );
        $this->set_database_table($model->get_table());

        $column_prefix = $this->database_table->get_column_prefix();

        # create columns
        foreach ($model->properties as $property) {
            $this->add_column(
                name: $column_prefix . $property->key,
                title: $property->singular_name,
                type: $property->sql_type,
                sortable: $property->is_indexable,
                visible: true
            );
        }

        # add screen option
        $this->add_screen_option((new ScreenOption('Per Page', $this->max_rows)));
    }
}
