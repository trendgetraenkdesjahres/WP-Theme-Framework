<?php

namespace WP_Framework\Model\Property;

use WP_Framework\Database\Database;
use WP_Framework\Model\CustomModel;

class ForeignProperty extends Property
{
    public string $reference_table;

    public string $reference_id_column;

    public function __construct(
        CustomModel $referenced_model,
        bool $nullable = false,
        bool $is_indexable = false,
        $default_value = null)
    {
        $this->reference_table = Database::create_model_table_name($referenced_model->name);
        $this->reference_id_column = "{$referenced_model->name}_id";

        parent::__construct(
            singular_name: $referenced_model->singular_name,
            plural_name: $referenced_model->plural_name,
            sql_type: 'bigint(20)',
            nullable: $nullable,
            is_indexable: $is_indexable,
            default_value: $default_value
        );
    }
}