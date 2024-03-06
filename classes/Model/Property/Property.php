<?php

namespace WP_Framework\Model\Property;

use WP_Framework\Database\SQLSyntax;

class Property
{
    public string $sql_type;
    public string $default_value;
    public string $nullable;

    /**
     * Creates a property for a custom model.
     *
     * @param string $key          The internal name of the property.
     * @param string $title         The display title.
     * @param string $sql_type      The SQL data type of the property. Accepts: bigint (unsigned), varchar, int (unsigned), text, tinytext, datetime
     * @param bool   $nullable      Indicates whether the property is nullable.
     * @param bool   $is_indexable  Indicates whether the property is indexable.
     * @param mixed  $default_value The default value for the property.
     *
     * @throws \Error If the key, type, or SQL type is invalid.
     */
    public function __construct(public string $key, string $sql_type, public string $name, public string $plural_name, bool $nullable = false, public bool $is_indexable = false, $default_value = null)
    {
        # add validated type
        if (!SQLSyntax::data_type($sql_type)) {
            throw new \Error("The {$key}'s type '{$sql_type}' is illegal.");
        }
        $this->sql_type = $sql_type;

        # add default value (no checks)
        $this->default_value = $default_value !== null ? "default '$default_value'" : '';

        # add NOT NULL value (no checks)
        $this->nullable = $nullable ? '' : 'NOT NULL';
    }

    public function __toString()
    {
        return $this->key;
    }
}
