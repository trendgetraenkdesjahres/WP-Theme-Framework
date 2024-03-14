<?php

namespace WP_Framework\Model\Property;

use WP_Framework\Database\SQLSyntax;
use WP_Framework\Element\Input\FormControlElement;
use WP_Framework\Model\CustomModel;

class Property
{
    private static array $illegal_property_keys = [
        'model_name',
        'id'
    ];
    /**
     * @var string The internal name of the property.
     */
    public string $key;

    /**
     * @var string The SQL data type of the property.
     * Accepts: bigint (unsigned), varchar, int (unsigned), text, tinytext, datetime.
     */
    public string $sql_type;

    /**
     * @var string|null The size associated with the SQL data type (e.g., varchar size).
     */
    public ?string $sql_type_size;

    /**
     * @var string The PHP data type of the property.
     */
    public string $php_type;

    /**
     * @var FormControlElement|null The form control element associated with the property. Will try to auto-build if null.
     */
    public ?FormControlElement $form_control = null;

    /**
     * @var string The default value for the property.
     */
    public string $default_value;


    /**
     * @var string Specifies whether the property is nullable.
     */
    public string $nullable;

    /**
     * Creates a property for a custom model.
     *
     * @param string $singular_name The singular name of the property.
     * @param string $plural_name   The plural name of the property.
     * @param string $sql_type      The SQL data type of the property.
     * @param string|null $key      The internal name of the property. If not provided, derived from the singular name.
     * @param bool   $nullable      Indicates whether the property is nullable.
     * @param bool   $is_indexable  Indicates whether the property is indexable.
     * @param mixed  $default_value The default value for the property.
     *
     * @throws \Error If the key, type, or SQL type is invalid.
     */
    public function __construct(public string $singular_name, public string $plural_name, string $sql_type, ?string $key = null, bool $nullable = false, public bool $is_indexable = false, $default_value = null)
    {
        $this->set_types($sql_type);
        $this->set_names($singular_name, $plural_name, $key);

        # add default value (no checks)
        $this->default_value = $default_value !== null ? "default '$default_value'" : '';

        # add NOT NULL value (no checks)
        $this->nullable = $nullable ? '' : 'NOT NULL';
    }

    /**
     * Set the SQL type and size based on the provided SQL type.
     *
     * @param string $sql_type The SQL data type of the property.
     *
     * @return Property
     * @throws \Error If the SQL type is invalid.
     */
    private function set_types(string $sql_type): Property
    {
        if (!SQLSyntax::is_data_type($sql_type)) {
            throw new \Error("The SQL type '{$sql_type}' is illegal.");
        }
        $type_info = SQLSyntax::get_type_info($sql_type);

        $this->sql_type = $type_info['type'];
        $this->sql_type_size = $type_info['size'];

        if (in_array($this->sql_type, ['int', 'bigint'])) {
            $this->php_type = 'int';
        } else {
            $this->php_type = 'string';
        }

        return $this;
    }

    /**
     * Set the internal names based on the provided singular and plural names.
     *
     * @param string $singular_name The singular name of the property.
     * @param string $plural_name   The plural name of the property.
     * @param string|null $key      The internal name of the property.
     *
     * @return Property
     * @throws \Error If the key is invalid.
     */
    private function set_names(string $singular_name, string $plural_name, ?string $key = null): Property
    {
        if (!$key) {
            $key = sanitize_key($singular_name);
        }
        if (in_array($key, self::$illegal_property_keys)) {
            throw new \Error("The key '{$key}' is illegal.");
        }
        if (!SQLSyntax::is_field_name($key)) {
            throw new \Error("The key '{$key}' is illegal.");
        }
        $this->key = $key;

        $this->plural_name = $plural_name;
        $this->singular_name = $singular_name;

        return $this;
    }

    /**
     * Get the value of the property for the given object ID.
     *
     * @param int|null $object_id The ID of the object.
     *
     * @return mixed The object ID.
     */
    public function get_value(?int $object_id = null): mixed
    {
        return $object_id;
    }

    /**
     * Get the form control element for the property.
     *
     * @param mixed $value The value to set on the form control.
     *
     * @return FormControlElement The form control element.
     */
    public function get_form_control($value): FormControlElement
    {
        $form_control = $this->form_control;
        if (!$form_control) {
            $form_control = $this->get_auto_form_control();
        }
        return $form_control
            ->set_value($value)
            ->set_id($this->key);
    }

    /**
     * Get an auto-generated form control element based on the property's SQL type.
     *
     * @return FormControlElement The auto-generated form control element.
     * @throws \Error If the form control could not be auto-built.
     */
    private function get_auto_form_control(): FormControlElement
    {
        $tag = '';
        $attributes = [];

        switch ($this->sql_type) {
            case 'varchar':
                if ($this->sql_type_size > 80) {
                    $tag = 'textarea';
                    $attributes['maxlength'] = $this->sql_type_size;
                } else {
                    $tag = 'input';
                    $attributes['type'] = 'text';
                    $attributes['maxlength'] = $this->sql_type_size;
                }
                break;

            case 'text':
                $tag = 'textarea';
                $attributes['maxlength'] = $this->sql_type_size;
                break;

            case 'tinytext':
                $tag = 'input';
                $attributes['type'] = 'text';
                $attributes['maxlength'] = $this->sql_type_size;
                break;

            case 'datetime':
                $tag = 'input';
                $attributes['type'] = 'datetime-local';
                break;

            case 'bigint':
            case 'int':
                $tag = 'input';
                $attributes['type'] = 'number';
                break;

            default:
                throw new \Error("Could not auto-build form.");
        }
        return new FormControlElement($tag, $attributes);
    }


    /**
     * Register a form control element for the property.
     *
     * @param FormControlElement $element The form control element to register.
     *
     * @return Property
     * @throws \Error If a form control is already registered.
     */
    public function register_form_control(FormControlElement $element): Property
    {
        if (isset($this->form_control)) {
            throw new \Error("There is already one Form Control registred.");
        }
        $this->form_control = $element;
        return $this;
    }

    /**
     * Unregister the form control element for the property.
     *
     * @return Property
     */
    public function unregister_form_control(): Property
    {
        $this->form_control = null;
        return $this;
    }

    /**
     * Get the fully qualified key for the property based on the model's sanitized name. For the naming of the SQL Table Columns.
     *
     * @param CustomModel $model The custom model associated with the property.
     *
     * @return string The fully qualified key.
     * @throws \Error If the fully qualified key is invalid.
     */
    public function get_property_key(CustomModel $model): string
    {
        $key = "{$model->name}_{$this->key}";
        if (!SQLSyntax::is_field_name($key)) {
            throw new \Error("The {$model->name}-property key '{$key}' is illegal.");
        }
        return $key;
    }

    /**
     * Get the string representation of the property (its internal name).
     *
     * @return string The internal name of the property.
     */
    public function __toString()
    {
        return $this->key;
    }
}
