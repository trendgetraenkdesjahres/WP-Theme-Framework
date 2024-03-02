<?php

namespace WP_Framework\Model;

/**
 * Handles the DataModel to implement own models in WordPress.
 */
class DataModel extends AbstractModel implements ObjectInterface
{
    protected string $type_class = 'DataType';
    protected string $types_json_folder = '';

    public function __construct(protected readonly string $name = '')
    {

        $types_json_folder =
    }

    public function get_buildin_object(int $object_id): object
    {
        throw new \Error("'$this->name' is not a built in model.");
    }
}
