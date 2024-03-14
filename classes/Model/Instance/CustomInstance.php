<?php

namespace WP_Framework\Model\Instance;

use WP_Framework\Database\Database;
use WP_Framework\Debug\Debug;
use WP_Framework\Model\CustomModel;


#[\AllowDynamicProperties]
class CustomInstance
{
    protected readonly string $model_name;
    protected readonly int $id;

    # construct a new instance of this model with this properties (if they match)
    public function __construct(CustomModel $model, array $properties)
    {
        $this->model_name = $model->name;
        $this->id = $properties[$model->name . "_id"];
        foreach ($model->get_properties() as $key => $property_obj) {
            # get the value
            $value = $properties[$key];

            # typecast value if it is an int
            if ($property_obj->php_type == 'int') {
                $value = (int) $value;
            }
            $this->{$property_obj->key} = $value;
        }
    }

    public static function get_instance(CustomModel $model, int $id): ?self
    {
        $table_name = $model->get_table_name();

        # try from cache
        $instance = self::get_instance_from_cache($table_name, $id);
        if ($instance) {
            return $instance;
        }

        # construct new instance
        $properties = self::get_properties_from_database($table_name, $id);
        if (!$properties) {
            return null;
        }
        $properties[$model->name . '_id'] = $id;
        $instance = new self($model, $properties);

        # cache new instance
        self::add_instance_to_cache($instance, $table_name, $id);
        return $instance;
    }

    private static function get_instance_from_cache(string $model_table_name, int $id): self|null
    {
        $success = false;
        $instance = wp_cache_get(
            key: $id,
            group: $model_table_name,
            found: $success
        );
        if (!$success) {
            return null;
        }
        return $instance;
    }

    private static function add_instance_to_cache(self $instance, string $model_table_name, int $id): bool
    {
        return wp_cache_add(
            key: $id,
            data: $instance,
            group: $model_table_name
        );
    }

    private static function get_properties_from_database(string $model_table_name, int $id): ?array
    {
        return Database::get_table($model_table_name)->get_row($id);
    }
}
