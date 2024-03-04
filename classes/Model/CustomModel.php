<?php

namespace WP_Framework\Model;

use WP_Framework\Database\Database;
use WP_Framework\Database\SQLSyntax;

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
    public  function __construct(string $name, public bool $has_meta, public bool $has_types, public bool $is_hierarchical, public ?string $owner_type = null)
    {
        $table_name = Database::$table_prefix . "_$name";
        if (!SQLSyntax::field_name($table_name)) {
            throw new \Error("The table-name '$table_name' of is illegal.");
        }

        $this->name = $name;
        $this->table_name = $table_name;
    }

    /**
     * Adds a property to the model.
     *
     * @param string $key           The key of the property.
     * @param string $sql_type      The SQL data type of the property. Accepts: bigint (unsigned), varchar, int (unsigned), text, tinytext, datetime
     * @param bool   $nullable      Indicates whether the property is nullable.
     * @param bool   $is_indexable  Indicates whether the property is indexable.
     * @param mixed  $default_value The default value for the property.
     * @return CustomModel
     *
     * @throws \Error If the key, type, or SQL type is invalid.
     */
    public function add_property(string $key, string $sql_type, bool $nullable = false, bool $is_indexable = false, mixed $default_value = null): CustomModel
    {
        $property = [];

        # add validated key
        $prefixed_key = "{$this->name}_{$key}";
        if (!SQLSyntax::field_name($prefixed_key)) {
            throw new \Error("The {$this->name}-property key '{$prefixed_key}' is illegal.");
        }
        $property['key'] = $prefixed_key;

        # add validated type
        if (!SQLSyntax::data_type($sql_type)) {
            throw new \Error("The {$prefixed_key}'s type '{$sql_type}' is illegal.");
        }
        $property['type'] = $sql_type;

        # add default value (no checks)
        $property['default_value'] = $default_value !== null ? "default '$default_value'" : '';

        # add this property to the composite index array
        if ($is_indexable) {
            if (!SQLSyntax::indexable_data_type($sql_type)) {
                throw new \Error("The SQL Type '$sql_type' is not indexable.");
            }
            array_push($this->composite_index_properties, $key);
        }

        # add NOT NULL value (no checks)
        $property['nullable'] = $nullable ? '' : 'NOT NULL';

        # add new portperty
        array_push($this->properties, $property);

        return $this;
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
