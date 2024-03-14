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
    protected string $table_name;

    # construct a new instance of this model with this properties (if they match)
    public function __construct(CustomModel $model, array $properties)
    {
        $this->table_name = $model->get_table_name();
        
        foreach ($model->get_properties() as $key => $property_obj) {
            # get the value
            $value = $properties[$key];

            # typecast value if it is an int
            if($property_obj->php_type == 'int') {
                $value = (int) $value;
            }

            $this->{$property_obj->key} = $value;
        }
    }
}
