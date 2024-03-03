<?php

namespace WP_Framework\Model;

/**
 * DataModel is the class to implement custom models.
 *
 * @package WP_Framework\Model
 */
class DataModel extends AbstractModel implements ModelInterface
{
    /**
     * @var string|null The class name for data types.
     */
    protected ?string $type_class = 'DataType';

    /**
     * @var string|null The folder containing JSON files for data types.
     */
    protected ?string $types_json_folder = null;

    /**
     * @var string The name of the table.
     */
    protected readonly string $table_name;

    /**
     * @var array An array to store properties of the model.
     */
    protected array $properties = [];

    /**
     * @var array An array to store properties eligible for composite indexing.
     */
    protected array $composite_index_properties = [];

    /**
     * DataModel constructor.
     * set to private, to disable usage with 'new'.
     *
     * @param string      $name           The name of the data model.
     * @param bool        $has_meta       Indicates whether the model has meta data.
     * @param bool        $has_types      Indicates whether the model has types.
     * @param bool        $is_hierarchical Indicates whether the model is hierarchical.
     * @param string|null $owner_type      The type of owner (e.g., 'author' or 'user').
     */
    private function __construct(string $name, protected bool $has_meta, protected bool $has_types, private bool $is_hierarchical, private ?string $owner_type = null)
    {
        $table_name = framework()->table_prefix . "_$name";
        if (!self::is_valid_sql_field_name($table_name)) {
            throw new \Error("The constructed table-name '$table_name' is illegal.");
        }

        $this->name = $name;
        $this->table_name = $table_name;
    }

    /**
     * Static alias for the constructor.
     *
     * @param string      $name           The name of the data model.
     * @param bool        $has_meta       Indicates whether the model has meta data.
     * @param bool        $has_types      Indicates whether the model has types.
     * @param bool        $is_hierarchical Indicates whether the model is hierarchical (objects have parents).
     * @param string|null $owner_type      The type of owner (e.g., 'author' or 'user').
     *
     * @return DataModel The created DataModel instance.
     */
    public static function create(string $name, bool $has_meta = false, bool $has_types = false, bool $is_hierarchical = false, ?string $owner_type = null)
    {
        return new self($name, $has_meta, $has_types, $is_hierarchical, $owner_type);
    }

    /**
     * Adds a property to the model.
     *
     * @param string $key           The key of the property.
     * @param string $sql_type      The SQL data type of the property. Accepts: bigint (unsigned), varchar, int (unsigned), text, tinytext, datetime
     * @param bool   $nullable      Indicates whether the property is nullable.
     * @param bool   $is_indexable  Indicates whether the property is indexable.
     * @param mixed  $default_value The default value for the property.
     * @return DataModel
     *
     * @throws \Error If the key, type, or SQL type is invalid.
     */
    public function add_property(string $key, string $sql_type, bool $nullable = false, bool $is_indexable = false, mixed $default_value = null): DataModel
    {
        $property = [];

        # add validated key
        $prefixed_key = "{$this->name}_{$key}";
        if (!self::is_valid_sql_field_name($prefixed_key)) {
            throw new \Error("The {$this->name}-property key '{$prefixed_key}' is illegal.");
        }
        $property['key'] = $prefixed_key;

        # add validated type
        if (!self::is_valid_sql_type($sql_type)) {
            throw new \Error("The {$prefixed_key}'s type '{$sql_type}' is illegal.");
        }
        $property['type'] = $sql_type;

        # add default value (no checks)
        $property['default_value'] = $default_value !== null ? "default '$default_value'" : '';

        # add this property to the composite index array
        if ($is_indexable) {
            if (!self::is_indexable_sql_type($sql_type)) {
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

    /**
     * Performs the migration to create tables in the database.
     *
     * @return DataModel
     */
    public function migrate(): DataModel
    {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta(
            $this->get_migration_query($wpdb->get_charset_collate())
        );
        return $this;
    }

    /**
     * Generates the migration SQL query.
     *
     * @return string The SQL query for migration.
     */
    private function get_migration_query(): string
    {
        $query = '';
        # get database character collate
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        # is defined in wp_get_db_schema
        $max_index_length = 19;

        # construct query for meta table
        if ($this->has_meta) {
            $query .= "CREATE TABLE {$this->table_name}meta (";
            $query .= "meta_id bigint(20) unsigned NOT NULL auto_increment,";
            $query .= "{$this->name}_id bigint(20) unsigned NOT NULL default '0',";
            $query .= "meta_key varchar(255) default NULL,";
            $query .= "meta_value longtext,";
            $query .= "PRIMARY KEY (meta_id),";
            $query .= "KEY {$this->name}_id ({$this->name}_id),";
            $query .= "KEY meta_key (meta_key($max_index_length))";
            $query .= ") {$charset_collate};\n";
        }

        # construct for main table query
        $query .= "CREATE TABLE {$this->table_name}s (";

        # add primary key
        $query .= "{$this->name}_id bigint(20) unsigned NOT NULL auto_increment, PRIMARY KEY ({$this->name}_id),";

        # iterate through the properties and add
        foreach ($this->properties as $propery) {
            $query .= "{$propery['key']} {$propery['type']} {$propery['nullable']} {$propery['default_value']},";
        }

        # add type...
        if ($this->has_types) {
            array_push($this->composite_index_properties, 'type');
            $query .= "{$this->name}_type varchar(20) NOT NULL default '{$this->name}',";
        }

        # add hirarchie key
        if ($this->is_hierarchical) {
            $query .= "{$this->name}_parent bigint(20) unsigned NOT NULL default '0', KEY {$this->name}_parent ({$this->name}_parent),";
        }

        # add owner (like 'author' or 'user')
        if (is_string($this->owner_type)) {
            $query .= "{$this->name}_{$this->owner_type} bigint(20) unsigned NOT NULL default '0', KEY {$this->name}_{$this->owner_type} ({$this->name}_{$this->owner_type}),";
        }

        # add composite index
        if ($this->composite_index_properties) {
            $key_name = implode('_', $this->composite_index_properties);
            $key_list = '';
            foreach ($this->composite_index_properties as $key) {
                $key_list .= "{$this->name}_{$key},";
            }
            $key_list = rtrim($key_list, ',');
            $query .= "KEY {$key_name} ($key_list),";
        }
        $query = rtrim($query, ',');

        # close and return the query with charset collate
        return $query . ") {$charset_collate};\n";
    }

    /**
     * Checks if a given string is a valid SQL field name.
     *
     * @param string $field_name The table name to check.
     *
     * @return bool True if the field name is valid; otherwise, false.
     */
    private static function is_valid_sql_field_name(string $field_name): bool
    {
        $first_character = $field_name[0];
        if (!ctype_lower($first_character)) {
            return false;
        }
        foreach (str_split($field_name) as $character) {
            if (!(ctype_lower($character) || $character == '_')) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks if a given string is a valid SQL data type.
     *
     * @param string $type The SQL data type to check.
     *
     * @return bool True if the data type is valid; otherwise, false.
     */
    private static function is_valid_sql_type(string $type): bool
    {
        return (bool) preg_match(
            pattern: '/^(bigint\((\d+)\)|varchar\((\d+)\)|int\((\d+)\)|text|tinytext|datetime)\s*(unsigned)?$/',
            subject: $type
        );
    }

    /**
     * Checks if a given SQL data type is indexable.
     *
     * @param string $type The SQL data type to check.
     *
     * @return bool True if the data type is indexable; otherwise, false.
     */
    private static function is_indexable_sql_type(string $type): bool
    {
        $matches = [];
        if (!preg_match(
            pattern: '/^(bigint\((\d+)\)|varchar\((\d+)\)|int\((\d+)\)|datetime)\s*(unsigned)?$/',
            subject: $type,
            matches: $matches
        )) {
            return false;
        }
        foreach ($matches as $match) {
            if (!$match) continue;
            if ($match === $type) continue;
            if ($match > 20) {
                return false;
            }
        }
        return true;
    }
}
