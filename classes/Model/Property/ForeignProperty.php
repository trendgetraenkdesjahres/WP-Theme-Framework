<?php

namespace WP_Framework\Model\Property;

use WP_Framework\Database\Database;
use WP_Framework\Model\AbstractModel;
use WP_Framework\Model\CustomModel;

class ForeignProperty extends Property
{
    public string $reference_table;

    public string $reference_id_column;

    public function __construct(AbstractModel $referenced_model)
    {
        $this->reference_table = Database::create_model_table_name($referenced_model->name);
        $this->reference_id_column = "{$referenced_model->name}_id";

        # get fancy names
        if($referenced_model instanceof CustomModel) {
            $singular_name = $referenced_model->singular_name;
            $plural_name = $referenced_model->plural_name;
        } else {
            $singular_name = $referenced_model->name;
            $plural_name = $singular_name. 's';
        }

        parent::__construct(
            singular_name: $singular_name,
            plural_name: $plural_name,
            sql_type: 'bigint(20)',
            is_indexable: true
        );
    }
}