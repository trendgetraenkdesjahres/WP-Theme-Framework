<?php

namespace WP_Framework\Model\Property;

use WP_Framework\Model\BuildinModel;
use WP_Framework\Model\CustomModel;

class ForeignProperty extends Property
{
    public string $reference_table;

    public string $reference_id_column;

    public function __construct(BuildinModel|CustomModel $referenced_model)
    {
        $this->reference_table = $referenced_model->get_table()->name;
        $this->reference_id_column = $referenced_model->get_table()->id_column_name;

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