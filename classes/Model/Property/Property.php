<?php

namespace WP_Framework\Model\Property;

use WP_Framework\Database\SQLSyntax;
use WP_Framework\Element\Input\FormControlElement;

class Property
{
    public string $sql_type;
    public string $default_value;
    public string $nullable;

    /**
     * Creates a property for a custom model.
     *
     * @param string $key          The internal name of the property.
     * @param string $sql_type      The SQL data type of the property. Accepts: bigint (unsigned), varchar, int (unsigned), text, tinytext, datetime
     * @param string $singular_name
     * @param string $plural_name
     * @param bool   $nullable      Indicates whether the property is nullable.
     * @param bool   $is_indexable  Indicates whether the property is indexable.
     * @param mixed  $default_value The default value for the property.
     *
     * @throws \Error If the key, type, or SQL type is invalid.
     */
    public function __construct(public string $key, string $sql_type, public string $singular_name, public string $plural_name, bool $nullable = false, public bool $is_indexable = false, $default_value = null)
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

    public function get_value(?int $object_id = null): mixed
    {
        return $object_id;
    }

    public function get_form_control_element($value): FormControlElement
    {
        $matches = [];
        preg_match(
            pattern: '/^(\w+)(\(\d+\))?/',
            subject: $this->sql_type,
            matches: $matches
        );
        $type = $matches[1];
        $length = isset($matches[2]) ? trim($matches[2], '()') : null;

        $tag_name = '';
        $tag_attributes = ['id' => $this->key];
        $tag_attributes['value'] = $value;

        switch ($type) {
            case 'bigint':
                $tag_name = 'input';
                $tag_attributes['type'] = 'number';
                break;

            case 'int':
                $tag_name = 'input';
                $tag_attributes['type'] = 'number';
                break;

            case 'datetime':
                $tag_name = 'input';
                $tag_attributes['type'] = 'datetime-local';
                break;

            case 'text':
                $tag_name = 'textarea';
                break;

            case 'tinytext':
                $tag_name = 'input';
                $tag_attributes['type'] = 'text';
                break;

            case 'varchar':
                if ($length && $length > 80) {
                    $tag_name = 'textarea';
                    $tag_attributes['maxlength'] = $length;
                    break;
                }
                if ($length) {
                    $tag_name = 'input';
                    $tag_attributes['type'] = 'text';
                    $tag_attributes['maxlength'] = $length;
                    break;
                }
                $tag_name = 'input';
                $tag_attributes['type'] = 'text';
                break;
        }

        return new FormControlElement($tag_name, $tag_attributes, '');
    }

    public function __toString()
    {
        return $this->key;
    }
}
