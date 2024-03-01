<?php

namespace WP_ThemeFramework\MetaField;

/**
 * A factory class for creating instances of MetaFieldInterface implementations.
 */
class MetaFactory
{
    /**
     * @var string The class name of the MetaFieldInterface implementation to create.
     */
    private string $class;

    /**
     * Creates a new MetaFieldFactory instance.
     *
     * @param string $class The class name to fabricate the MetaFields of.
     */
    public function __construct(string $class)
    {
        $class = __NAMESPACE__ . "\\$class";
        if (!class_exists($class)) {
            throw new \Error("'$class' is not a class name.");
        }
        if (!is_subclass_of($class, __NAMESPACE__ . '\MetaField')) {
            throw new \Error("'$class' is not a MetaField.");
        }
        $this->class = $class;
    }

    /**
     * Creates a number input meta field.
     *
     * @param string $meta_name The name of the meta field.
     * @param string $description The description of the meta field.
     * @param array $attributes Additional attributes for the input element.
     * @return MetaFieldInterface The created meta field.
     */
    public function create_number(string $meta_name, string $description, string|array $assign_to_type, array $attributes = []): MetaFieldInterface
    {
        return new $this->class(
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
     * @return MetaFieldInterface The created meta field.
     * @throws \Error If 'min' and 'max' attributes are not provided.
     */
    public function create_number_range(string $meta_name, string $description, array $attributes): MetaFieldInterface
    {
        if (!isset($attributes['min']) || !isset($attributes['min'])) {
            throw new \Error("'min' and 'max' attributes are necessary.");
        }
        return new $this->class(
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
     * @return MetaFieldInterface The created meta field.
     */
    public function create_text(string $meta_name, string $description, array $attributes = []): MetaFieldInterface
    {
        return new $this->class(
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
     * @return MetaFieldInterface The created meta field.
     */
    public function create_text_multiline(string $meta_name, string $description, array $attributes = []): MetaFieldInterface
    {
        return new $this->class(
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
     * @return MetaFieldInterface The created options meta field.
     */
    public function create_options(string $meta_name, string $description, array $options, array $attributes = []): MetaFieldInterface
    {
        return new $this->class(
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
     * @return MetaFieldInterface The created meta field instance.
     */
    public function create_bool(string $meta_name, string $description, array $attributes = []): MetaFieldInterface
    {
        return new $this->class(
            name: $meta_name,
            description: $description,
            input_element_type: 'checkbox',
            input_element_attributes: $attributes
        );
    }
}