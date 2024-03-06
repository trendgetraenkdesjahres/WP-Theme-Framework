<?php

namespace WP_Framework\Model;

use WP_Framework\AdminPanel\Table\AbstractTable;
use WP_Framework\AdminPanel\Table\ModelTable;
use WP_Framework\Database\Database;
use WP_Framework\Database\SQLSyntax;
use WP_Framework\Model\Property\Property;

/**
 * DataModel is the class to implement custom models.
 *
 * @package WP_Framework\Model
 */
class CustomModel extends AbstractModel
{
    /**
     * @var string|null The class name for data types.
     */
    public ?string $type_class = 'DataType';

    /**
     * @var string|null The folder containing JSON files for data types.
     */
    public ?string $types_json_folder = null;

    /**
     * @var string The name of the table.
     */
    public readonly string $table_name;

    /**
     * @var string The internal name of the model.
     */
    public readonly string $sanitized_name;

    /**
     * @var array An array to store properties of the model.
     */
    public array $properties = [];

    /**
     * @var array An array to store properties eligible for composite indexing.
     */
    public array $composite_index_properties = [];

    /**
     * DataModel constructor.
     * set to private, to disable usage with 'new'.
     *
     * @param string      $name           The name of the data model.
     * @param bool        $has_meta       Indicates whether the model has meta data.
     * @param bool        $has_types      Indicates whether the model has types.
     * @param bool        $is_hierarchical Indicates whether the model is hierarchical (objects have parents).
     * @param string|null $owner_type      The type of owner (e.g., 'author' or 'user').
     */
    public  function __construct(public string $name, public ?string $plural_name = null, public bool $has_meta = false, public bool $has_types = false, public bool $is_hierarchical = false, public ?string $owner_type = null)
    {
        $this->sanitized_name = sanitize_key($name);
        $this->table_name = Database::$table_prefix . "_" . $this->sanitized_name;
        if (!SQLSyntax::field_name($this->table_name)) {
            throw new \Error("The table-name '$this->table_name' of is illegal.");
        }

        if (!$plural_name) {
            $plural_name = $name . 's';
        }
        $this->plural_name = $plural_name;

        if ($this->has_types) {
            $type_property = new Property(
                key: 'type',
                sql_type: 'varchar(20)',
                name: "{$this->name} Type",
                plural_name: "{$this->name} Types",
                is_indexable: true,
                default_value: $this->sanitized_name
            );
            $this->register_property($type_property);
        }
    }

    public function register_property(Property ...$property): CustomModel
    {
        foreach ($property as $property) {
            # validate key
            $key = "{$this->sanitized_name}_{$property->key}";
            if (!SQLSyntax::field_name($key)) {
                throw new \Error("The {$this->name}-property key '{$key}' is illegal.");
            }

            # append key
            $this->properties[$key] = $property;
        }
        return $this;
    }

    public function get_properties(bool $just_indexables = false): array
    {
        if (!$just_indexables) {
            return $this->properties;
        }
        $array = [];
        foreach ($this->properties as $property) {
            if ($property->is_indexable) {
                array_push($array, $property);
            }
        }
        return $array;
    }

    public function get_custom_model_table_name(): string
    {
        return Database::$table_prefix . "_" . $this->sanitized_name . "s";
    }

    public function get_custom_model_meta_table_name(): string
    {
        return Database::$table_prefix . "_" . $this->sanitized_name . "meta";
    }

    public function get_panel_table(): ModelTable
    {
        if (!$this->panel_table) {
            $this->panel_table = new ModelTable($this);
        }
        return $this->panel_table;
    }


    public function register()
    {
        /*
        - add to menu
        - add a screen
        - add an editor
         */
    }

    public function unregister()
    {
    }

    public function is_registered()
    {
    }
}
