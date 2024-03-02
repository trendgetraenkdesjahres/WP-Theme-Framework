<?php

namespace WP_Framework\Model\Type\Meta;

use DOMDocument;

/**
 * Interface for custom meta fields in WordPress.
 */
interface MetaInterface
{
    /**
     * Registers the object-type's meta field.
     * @param string $assign_to_object_type The type of object ('post', 'page', 'category'...).
     *
     * @return MetaInterface The current instance of PostMeta.
     */
    public function register(string $assign_to_object_type): MetaInterface;

    /**
     * Unregisters the object-type's meta field.
     * @param string $assign_to_object_type The type of object ('post', 'page', 'category'...).
     *
     * @return TypeInterface The modified PostType instance.
     */
    public function unregister(string $assiged_to_object_type): MetaInterface;

    /**
     * Check if the custom object type is registered.
     *
     * @return bool True if the post type is registered, false otherwise.
     */
    public function is_registered(string $assiged_to_object_type = ''): bool;

    /**
     * Go get the thing
     *
     * @return string
     */
    public function get_meta_type(): string;
}

/**
 * Abstract class for creating custom meta fields in WordPress.
 */
abstract class Meta
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
     * Create a new Meta instance.
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

    public function get_meta_type(): string
    {
        return 'abstract ( :-0 ) abstract';
    }

    /**
     * Creates a number input meta field.
     *
     * @param string $meta_name The name of the meta field.
     * @param string $description The description of the meta field.
     * @param array $attributes Additional attributes for the input element.
     * @return MetaInterface The created meta field.
     */
    public static function create_number(string $meta_name, string $description, string|array $assign_to_type, array $attributes = []): MetaInterface
    {
        $meta_class = get_called_class();
        return new $meta_class(
            name: $meta_name,
            description: $description,
            input_element_type: 'number',
            input_element_attributes: $attributes
        );
    }

    /**
     * Creates a number range input meta field.
     *
     * @param string $meta_name The name of the meta field.
     * @param string $description The description of the meta field.
     * @param array $attributes Attributes for the input element. 'min' and 'max' are required.
     * @return MetaInterface The created meta field.
     * @throws \Error If 'min' and 'max' attributes are not provided.
     */
    public static function create_number_range(string $meta_name, string $description, array $attributes): MetaInterface
    {
        if (!isset($attributes['min']) || !isset($attributes['min'])) {
            throw new \Error("'min' and 'max' attributes are necessary.");
        }
        $meta_class = get_called_class();
        return new $meta_class(
            name: $meta_name,
            description: $description,
            input_element_type: 'range',
            input_element_attributes: $attributes
        );
    }

    /**
     * Creates a text input meta field.
     *
     * @param string $meta_name The name of the meta field.
     * @param string $description The description of the meta field.
     * @param array $attributes Additional attributes for the input element. 'maxlength' is recommended.
     * @return MetaInterface The created meta field.
     */
    public static function create_text(string $meta_name, string $description, array $attributes = []): MetaInterface
    {
        $meta_class = get_called_class();
        return new $meta_class(
            name: $meta_name,
            description: $description,
            input_element_type: 'text',
            input_element_attributes: $attributes
        );
    }

    /**
     * Creates a multiline text input meta field.
     *
     * @param string $meta_name The name of the meta field.
     * @param string $description The description of the meta field.
     * @param array $attributes Additional attributes for the input element. 'rows' and 'cols' are recommended.
     * @return MetaInterface The created meta field.
     */
    public static function create_text_multiline(string $meta_name, string $description, array $attributes = []): MetaInterface
    {
        $meta_class = get_called_class();
        return new $meta_class(
            name: $meta_name,
            description: $description,
            input_element_tag_name: 'textarea',
            input_element_attributes: $attributes
        );
    }

    /**
     * Creates an options meta field.
     *
     * @param string $meta_name The name of the meta field.
     * @param string $description The description of the meta field.
     * @param array $options The options for select input elements.
     * @param array $attributes Additional attributes for the input element.
     * @return MetaInterface The created options meta field.
     */
    public static function create_options(string $meta_name, string $description, array $options, array $attributes = []): MetaInterface
    {
        $meta_class = get_called_class();
        return new $meta_class(
            name: $meta_name,
            description: $description,
            input_element_tag_name: 'select',
            input_element_options: $options,
            input_element_attributes: $attributes
        );
    }


    /**
     * Creates a boolean (checkbox) meta field.
     *
     * @param string $meta_name The name of the meta field.
     * @param string $description The description of the meta field.
     * @param array $attributes Additional attributes for the checkbox input field.
     * @return MetaInterface The created meta field instance.
     */
    public static function create_bool(string $meta_name, string $description, array $attributes = []): MetaInterface
    {
        $meta_class = get_called_class();
        return new $meta_class(
            name: $meta_name,
            description: $description,
            input_element_type: 'checkbox',
            input_element_attributes: $attributes
        );
    }

    /**
     * Set options for the meta field
     * 'object_subtype', 'type', 'string', 'description' are set by the class.
     * @link https://developer.wordpress.org/reference/functions/register_meta/#parameters
     */
    public function set_option(string $option, $value): Meta
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
     * Check if saving is safe and the nonce is verified.
     *
     * @param int $object_id    The ID of the object.
     * @param string $object_type The type of the object.
     * @return bool True if saving is safe and secure, false otherwise.
     */
    protected function is_saving_safe_and_secure(int $object_id, string $object_type)
    {
        if (!self::is_safe_to_save($object_id, 'post')) {
            return false;
        }
        return $this->is_nonce_verified();
    }

    /**
     * Validate if it's safe to save the meta field.
     *
     * @param int $object_id The ID of the object.
     * @param string $class The class of the object.
     * @return bool True if it's safe to save, false otherwise.
     */
    private static function is_safe_to_save(int $object_id, string $object_type): bool
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
