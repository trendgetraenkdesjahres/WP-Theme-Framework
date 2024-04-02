<?php

namespace WP_Framework\Element\Input;

use WP_Framework\Element\Fragment;
use WP_Framework\Element\Element;

/**
 * Class FormControlElement
 * @package WP_Framework\Element\Input
 *
 * Represents a form control element (<input>, <select>, or <textarea>) with an additional description.
 * 'id' and 'value' attributes are appreciated, and for <select>, the 'selected' attribute.
 */
class FormControlElement extends Fragment
{
    public string $description;

    private array $attributes;
    private string $tag_name;

    private mixed $current_value;

    # factory methods // needs to be renamed
    public static function create_input_hidden(string $name, ?string $value)
    {
        return new FormControlElement('input', [
            'type' => 'hidden',
            'name' => $name,
            'value' => $value
        ], $name);
    }

    # factory methods
    public static function create_input_text(string $name, ?string $value)
    {
        return new FormControlElement('input', [
            'type' => 'text',
            'name' => $name,
            'value' => $value,
            'id' => $name,
            'placeholder' => "Insert {$name}"
        ], $name);
    }

    /**
     * FormControlElement constructor.
     *
     * @param string $tag_name The tag name for the form control element ('input', 'select', or 'textarea').
     * @param array $attributes The attributes for the form control element.
     * @param string $description The description for the form control element.
     * @param array $options Optional options for <select> elements ['value' => 'name', ...].
     */
    public function __construct(string $tag_name, array $attributes, ?string $description = null, $options = [], null|bool|int|string $current_value = null)
    {
        $this->tag_name = $tag_name;
        $this->description = $description;

        if (!isset($attributes['id'])) {
            $attributes['id'] = bin2hex(random_bytes(2));
        }
        $this->attributes = $attributes;

        $this->current_value = $current_value;

        # create options
        $select_option_elements = $this->create_select_option_elements($options);

        # create input tags
        switch ($tag_name) {
            case 'input':
                $user_input_element = $this->create_input_element($select_option_elements);
                $description_element = self::create_description_element($description, $attributes);
                break;
            case 'textarea':
                $user_input_element = new Element('textarea', $attributes, $attributes['value'] ?? '');
                $description_element = self::create_description_element($description, $attributes);
                break;
            case 'select':
                $description_element = self::create_description_element($description, $attributes);
                $user_input_element = new Element('select', $attributes, ...$select_option_elements);
                break;
            default:
                throw new \Error("No handling for '$tag_name' defined.");
        }
        parent::__construct($user_input_element, $description_element);
    }

    # returns empty array if options are empry
    private function create_select_option_elements(array $options): ?array
    {
        $option_elements = [];
        foreach ($options as $option_key => $option_name) {
            $option_attributes = ['value' => $option_key, 'label' => $option_name];
            if ($this->current_value === $option_key) {
                $option_attributes['selected'] = 'selected';
            }
            array_push($option_elements, new Element('option', $option_attributes));
        }
        return $option_elements;
    }

    private function create_input_element(array $option_elements = []): Element
    {
        if ($option_elements) {
            $list_id = $this->get_attribute('id') . '-list';
            $datalist_element = new Element(
                'datalist',
                ['id' => $list_id],
                ...$option_elements
            );
            $this->attributes['list'] = $list_id;
            return new Element('input', $this->attributes, $datalist_element);
        }
        if ($this->has_type_attribute('checkbox')) {
            $this->attributes['value'] = $this->current_value ? 'true' : 'false';
        }
        return new Element('input', $this->attributes);
    }



    private function create_description_element(): ?Element
    {
        if ($this->has_type_attribute('hidden')) {
            return null;
        }

        # build attributes
        $description_attributes = ['class' => 'description'];

        $description_attributes['id'] = $this->get_attribute('id') . "-description";

        # if we have a proper description
        if ($this->description) {
            return new Element('p', $description_attributes, $this->description);
        }

        # if we have a name
        if ($name = $this->get_attribute('name', false)) {
            $description = new Element('pre', $description_attributes, $name);
            return new Element('p', $description_attributes, $description);
        }

        # if we got nothing
        return new Element('p', $description_attributes, $this->get_attribute('id'));
    }

    public function get_tag_name(): string
    {
        return $this->tag_name;
    }

    public function get_data_type(): string
    {
        if ($this->get_tag_name() == 'textarea') {
            return 'string';
        }

        if ($this->get_tag_name() == 'select') {
            return 'string';
        }

        if ($this->get_attribute('type') == 'number' || $this->get_attribute('type') == 'range') {
            return 'integer';
        }

        if ($this->get_attribute('type') == 'text') {
            return 'string';
        }

        if ($this->get_attribute('type') == 'checkbox') {
            return 'boolean';
        }

        # default:
        return 'string';
    }

    public function get_attribute(string $attribute_name, bool $throw_error = true): string
    {
        if (!isset($this->attributes[$attribute_name])) {
            if ($throw_error) {
                throw new \Error("'{$attribute_name}' is not set.");
            }
            return false;
        }
        return $this->attributes[$attribute_name];
    }

    public function has_type_attribute(?string $type_attribute = null): string
    {
        if ($type_attribute === null) {
            return (bool) $this->get_attribute('type', false);
        }
        return $this->get_attribute('type', false) === $type_attribute;
    }

    protected function set_attribute(string $attribute_name, mixed $value, bool $override = false): static
    {
        # implement overriding
        if ($override === false) {
            if (isset($this->attributes[$attribute_name])) {
                throw new \Error("{$attribute_name} already set.");
            }
        }

        # implement manipulating the actual elements
        $this->node->firstChild->setAttribute($attribute_name, (string) $value);
        $this->attributes[$attribute_name] = $value;
        return $this;
    }

    public function set_value(mixed $value): static
    {
        # chechbox exception
        if ($this->get_data_type() === 'boolean') {
            return $value ? $this->set_attribute('checked', 'checked') : $this;
        }
        return $this->set_attribute('value', $value, true);
    }

    public function set_name_attribute(string $name): static
    {
        $this->set_attribute('name', $name, true);
        return $this;
    }

    public function set_id_attribute(string $form_id): static
    {
        $this->set_attribute('id', $form_id);
        return $this;
    }
}
