<?php

namespace WP_ThemeFramework\MetaField;

use DOMDocument;

/**
 * Interface for custom meta fields in WordPress.
 */
interface MetaFieldInterface
{
    /**
     * Register the meta field.
     *
     * @return MetaFieldInterface The registered MetaField instance.
     */
    public function register(string $assign_to_object_type): MetaFieldInterface;
}

/**
 * Abstract class for creating custom meta fields in WordPress.
 */
abstract class MetaField
{
    /**
     * The unique key for the meta field.
     */
    protected string $meta_key;

    /**
     * The title of the meta field.
     */
    protected string $title;


    /**
     * The type of data associated with this meta key.
     */
    protected string $description;

    /**
     * The position to display the input field (side, normal, advanced).
     */
    protected string $input_field_position;

    /**
     * The nonce name for the input field.
     */
    protected string $input_field_nonce_name;

    /**
     * The action name for the input field.
     */
    protected string $input_field_action_name;

    /**
     * The options for the meta field, with defaults
     * @link https://developer.wordpress.org/reference/functions/register_meta/#parameters
     */
    protected array $options = [];

    /**
     * Create a new MetaField instance.
     *
     * @param string $name The name of the meta field.
     * @param string|null $description Optional. The description of the meta field to display.
     * @param string $input_element_tag_name Optional. The HTML tag for the input element (input, textarea, select).
     * @param string|null $input_element_type Optional. The type of the input element (for input tag).
     * @param array|null $input_element_options Optional. The options for the input element (for select tag).
     * @param array|null $input_element_attributes Optional. Additional attributes for the input element.
     * @param string $display_position Optional. The position to display the input field (side, normal, advanced).
     */
    public function __construct(
        public readonly string $name,
        string $description = null,
        protected string $input_element_tag_name = 'input',
        protected ?string $input_element_type = null,
        protected ?array $input_element_options = null,
        protected ?array $input_element_attributes = null,
        protected string $display_position = 'side'
    ) {
        $this->meta_key = sanitize_title(get_stylesheet() . "-$name");
        $this->title = __($name, get_stylesheet());
        $this->description = esc_xml($description);

        $this->input_field_nonce_name = "{$this->meta_key}_nonce";
        $this->input_field_action_name = "{$this->meta_key}_action";

        $this->input_field_position = str_validate($display_position, 'side', 'normal', 'advanced');
    }

    /**
     * Set options for the meta field
     * 'object_subtype', 'type', 'string', 'description' are set by the class.
     * @link https://developer.wordpress.org/reference/functions/register_meta/#parameters
     */
    public function set_option(string $option, $value): MetaField
    {
        str_validate($option, 'default', 'single', 'sanitize_callback', 'auth_callback', 'show_in_rest', 'revisions_enabled');
        $this->options[$option] = $value;
        return $this;
    }

    public function get_data_type(): string
    {
        if ($this->input_element_tag_name == 'textarea') {
            return 'string';
        }

        if ($this->input_element_tag_name == 'select') {
            return 'string';
        }

        if ($this->input_element_type == 'number' || $this->input_element_type == 'range') {
            return 'integer';
        }

        if ($this->input_element_type == 'text') {
            return 'string';
        }

        if ($this->input_element_tag_name == 'checkbox') {
            return 'bool';
        }
    }

    protected function cast_to_data_type(mixed $var): string|int|bool
    {
        switch ($this->get_data_type()) {
            case 'string':
                return (string) $var;
            case 'integer':
                return (int) $var;
            case 'bool':
                return (bool) $var;
        }
    }

    /**
     * Get the current value of the meta field for a given object.
     *
     * @param int $object_id The ID of the object.
     * @param callable $get_method The method to retrieve the meta value (e.G. 'get_post_meta()').
     * @return mixed The current value of the meta field.
     */
    public function get_current_value(int $object_id, string $object_type): mixed
    {
        $value = get_metadata($object_type, $object_id, $this->meta_key, true);
        return $this->cast_to_data_type($value);
    }

    /**
     * Get the valid HTML representation of the input field.
     *
     * @param mixed $value Optional. The value to be displayed in the input field.
     * @return string The HTML representation of the input field.
     */
    protected function get_valid_input_field(mixed $value = ''): string
    {
        # copy elements
        $attributes = $this->input_element_attributes;

        # create dom and element
        $dom = new DOMDocument();
        $fragment = $dom->createDocumentFragment();

        # create input
        $input = $dom->createElement($this->input_element_tag_name);

        # create label
        $label = $dom->createElement('p');
        $label_text_node = $dom->createTextNode($this->description);
        $label->appendChild($label_text_node);
        $label->setAttribute('class', 'description');
        $label->setAttribute('id', "{$this->meta_key}-description");

        # prepare attributes and content/value
        $attributes['name'] = $this->meta_key;
        $attributes['id'] = $this->meta_key;

        switch ($this->input_element_tag_name) {
            case 'input':
                $attributes['value'] = $value;
                $attributes['type'] = $this->input_element_type;
                break;

            case 'textarea':
                $text_node = $dom->createTextNode($value);
                $input->appendChild($text_node);
                $attributes['type'] = $this->input_element_type;
                break;

            case 'select':
                foreach ($this->input_element_options as $option_value => $option_name) {
                    $option_node = $dom->createElement('option');
                    $option_name_node = $dom->createTextNode($option_name);
                    $option_node->appendChild($option_name_node);
                    $option_node->setAttribute('value', $option_value);
                    if ($option_value == $value) $option_node->setAttribute('selected', 'selected');
                    $input->appendChild($option_node);
                }
                $attributes['type'] = $this->input_element_type;
                break;

            default:
                throw new \Error("HTML Tag '$this->input_element_tag_name' is not accepted.");
        }

        # set attributes
        foreach ($attributes as $attribute => $value) {
            if (isset($value)) $input->setAttribute($attribute, $value);
        }

        $fragment->appendChild($input);
        $fragment->appendChild($label);
        return $dom->saveXML($fragment);
    }

    /**
     * Validate if it's safe to save the meta field.
     *
     * @param int $object_id The ID of the object.
     * @param string $class The class of the object.
     * @return bool True if it's safe to save, false otherwise.
     */
    protected static function is_safe_to_save(int $object_id, string $object_type): bool
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        if (!current_user_can("edit_{$object_type}", $object_id)) {
            return false;
        }

        if (is_multisite() && ms_is_switched()) {
            return false;
        }
        return true;
    }
}
