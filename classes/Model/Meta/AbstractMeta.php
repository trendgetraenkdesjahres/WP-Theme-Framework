<?php

namespace WP_Framework\Model\Meta;

use WP_Framework\Debug\Debug;
use WP_Framework\Element\AbstractElement;
use WP_Framework\Element\Input\FormControlElement;

/**
 * Abstract class for creating custom meta fields in WordPress.
 */
abstract class AbstractMeta
{
    /**
     * The unique key for the meta field.
     */
    public string $key;

    /**
     * The title of the meta field.
     */
    public string $name;

    /**
     * @var array The hooks used for editing the meta field.
     */
    protected array $edit_hooks;

    /**
     * @var array The hooks used for saving the meta field.
     */
    protected array $save_hooks;

    /**
     * @var string The type of the meta field (e.g., 'string').
     */
    public string $type = 'string';

    /**
     * @var string The description of the meta field.
     */
    public string $description;

    /**
     * @var string The position to display the input field (side, normal, advanced).
     */
    protected string $input_field_position;

    /**
     * @var string The nonce name for the input field.
     */
    protected string $input_field_nonce_name;

    /**
     * @var string The action name for the input field.
     */
    protected string $input_field_action_name;

    /**
     * @var array The options for the meta field, with defaults.
     * @link https://developer.wordpress.org/reference/functions/register_meta/#parameters
     */
    public array $options = [];

    protected FormControlElement $form_control_element;

    /**
     * Create a new Meta instance.
     *
     * @param string $title The name (which will be displayed) of the meta field.
     * @param string|null $description Optional. The description of the meta field to display.
     * @param string $input_element_tag_name Optional. The HTML tag for the input element (input, textarea, select).
     * @param string|null $input_element_type Optional. The type of the input element (for input tag).
     * @param array|null $input_element_options Optional. The options for the input element (for select tag).
     * @param array|null $input_element_attributes Optional. Additional attributes for the input element.
     * @param string $display_position Optional. The position to display the input field (side, normal, advanced).
     */
    public function __construct(
        string $name,
        FormControlElement $form_control,
        protected string $display_position = 'side'
    ) {
        $this->name = $name;
        $this->key = sanitize_title(get_stylesheet() . "-meta-{$this->name}");

        $this->form_control_element = $form_control;

        $this->input_field_nonce_name = "{$this->key}_nonce";
        $this->input_field_action_name = "{$this->key}_action";

        $this->input_field_position = str_validate($display_position, 'side', 'normal', 'advanced');
    }

    /**
     * Get the save callback function for the meta field.
     *
     * @param string $model_name The model associated with the meta field.
     * @return callable The save callback function.
     */
    abstract public function get_save_callback(string $model_name): callable;

    /**
     * Get the edit callback function for the meta field.
     *
     * @param string|null $model_name The type of the model associated with the meta field.
     * @return callable The edit callback function.
     */
    abstract public function get_edit_callback(?string $model_name = null): callable;


    /**
     * Set options for the meta field.
     * 'object_subtype', 'type', 'string', 'description' are set by the class.
     * @link https://developer.wordpress.org/reference/functions/register_meta/#parameters
     *
     * @param string $option The option to set.
     * @param mixed $value The value to set for the option.
     * @return AbstractMeta The current instance for method chaining.
     * @throws \Error If the provided option is not valid.
     */
    public function set_option(string $option, $value): AbstractMeta
    {
        str_validate($option, 'default', 'single', 'sanitize_callback', 'auth_callback', 'show_in_rest', 'revisions_enabled');
        $this->options[$option] = $value;
        return $this;
    }

    /**
     * Get the data type associated with the meta field.
     *
     * @return string The data type (e.g., 'string', 'integer', 'bool').
     */
    public function get_data_type(): string
    {
        if ($this->form_control_element->get_tag_name() == 'textarea') {
            return 'string';
        }

        if ($this->form_control_element->get_tag_name() == 'select') {
            return 'string';
        }

        if ($this->form_control_element->get_attribute('type') == 'number' || $this->form_control_element->get_attribute('type') == 'range') {
            return 'integer';
        }

        if ($this->form_control_element->get_attribute('type') == 'text') {
            return 'string';
        }

        if ($this->form_control_element->get_attribute('type') == 'checkbox') {
            return 'bool';
        }

        # default:
        return 'string';
    }

    /**
     * Cast a variable to the data type associated with the meta field.
     *
     * @param mixed $var The variable to cast.
     * @return string|int|bool The casted value.
     */
    protected function cast_to_data_type(mixed $var): string|int|bool
    {
        switch ($this->get_data_type()) {
            case 'string':
                return (string) $var;
            case 'integer':
                return (int) $var;
            case 'bool':
                if (is_string($var)) {
                    if ($var === 'true') {
                        return true;
                    }
                    if ($var === 'false') {
                        return false;
                    }
                }
                return (bool) $var;
        }
    }

    /**
     * Cast a variable to string (for storing in db).
     *
     * @param mixed $var The variable to cast.
     * @return string|int|bool The casted value.
     */
    protected function cast_to_string(mixed $var): string|int|bool
    {
        return (string) $var;
    }

    /**
     * Get the current value of the meta field for a given object.
     *
     * @param int $object_id The ID of the object.
     * @param string $omodel_name The type of the model.
     * @return mixed The current value of the meta field.
     */
    public function get_current_value(int $object_id, string $model_name): mixed
    {
        $value = get_metadata($model_name, $object_id, $this->key, true);
        return $this->cast_to_data_type($value);
    }

    /**
     * Get the valid HTML representation of the input field.
     *
     * @param mixed $value Optional. The value to be displayed in the input field.
     * @return string The HTML representation of the input field.
     */
    protected function get_form_control(mixed $value = ''): string
    {
        $this->form_control_element->set_value($value);
        return (string) $this->form_control_element;
    }

    /**
     * Get the hooks used for saving the meta field.
     *
     * @param string|null $model_name The type of the model associated with the meta field.
     * @return array The array of hooks used for saving the meta field.
     */
    public function get_save_hooks(?string $model_name = null): array
    {
        return $this->save_hooks;
    }

    /**
     * Get the hooks used for editing the meta field.
     *
     * @param string|null $model_name The name of the model associated with the meta field.
     * @return array The array of hooks used for editing the meta field.
     */
    public function get_edit_hooks(?string $model_name = null): array
    {
        return $this->edit_hooks;
    }

    /**
     * Check if saving is safe and the nonce is verified.
     *
     * @param int $object_id    The ID of the object.
     * @param string $model_name The name of the object's model.
     * @return bool True if saving is safe and secure, false otherwise.
     */
    protected function is_saving_safe_and_secure(int $object_id, string $model_name)
    {
        if (!self::is_safe_to_save($object_id, $model_name)) {
            return false;
        }
        return $this->is_nonce_verified();
    }

    /**
     * Validate if it's safe to save the meta field.
     *
     * @param int $object_id The ID of the object.
     * @param string $model_type The name of the object's model.
     * @return bool True if it's safe to save, false otherwise.
     */
    private static function is_safe_to_save(int $object_id, string $model_name): bool
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        if (!current_user_can("edit_{$model_name}", $object_id)) {
            return false;
        }

        if (is_multisite() && ms_is_switched()) {
            return false;
        }
        return true;
    }

    /**
     * Checks if the nonce is verified.
     *
     * @return bool Whether the nonce is verified.
     */
    private function is_nonce_verified()
    {
        if (!isset($_POST[$this->input_field_nonce_name])) {
            return false;
        }
        if (wp_verify_nonce($_POST[$this->input_field_nonce_name], $this->input_field_action_name)) {
            return true;
        }
        return false;
    }
}
