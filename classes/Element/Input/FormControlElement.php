<?php

namespace WP_Framework\Element\Input;

use WP_Framework\Debug\Debug;
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

    # factory methods
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
    public function __construct(private string $tag_name, private array $attributes, ?string $description = null, $options = [])
    {
        $value = isset($attributes['value']) ?  $attributes['value'] : null;
        # create options
        if ($options) {
            $option_elements = $this->get_select_option_elements($options, $value);
        } else {
            $option_elements = null;
        }

        # create input tags
        switch ($tag_name) {
            case 'input':
                if ($option_elements) {
                    $datalist_element = new Element('datalist', ['id' => $attributes['id'] . 's'], ...$option_elements);
                    $attributes['list'] = $attributes['id'] . 's';
                    $input_element = new Element('input', $attributes, $datalist_element);
                } else {
                    if (isset($attributes['type']) && $attributes['type'] == 'checkbox') {
                        $attributes['value'] = $value ? 'true' : 'false';
                    }
                    $input_element = new Element('input', $attributes);
                }
                if (isset($attributes['type']) && $attributes['type'] != 'hidden') {
                    $description_element = $this->get_description($description, $attributes);
                } else {
                    $description_element = null;
                }
                break;
            case 'textarea':
                $description_element = $this->get_description($description, $attributes);
                $input_element = new Element('textarea', $attributes, $attributes['value'] ?? '');
                break;
            case 'select':
                if ($options) {
                    $option_elements = $this->get_select_option_elements($options, $attributes['value']);
                }
                $description_element = $this->get_description($description, $attributes);
                $input_element = new Element('select', $attributes, ...$option_elements);
                break;
            default:
                throw new \Error("No handling for '$tag_name' defined.");
        }
        parent::__construct($input_element, $description_element);
    }

    private function get_select_option_elements(array $options, null|int|string $selected_value = null): array
    {
        $option_elements = [];
        foreach ($options as $option_value => $option_name) {
            $option_attributes = ['value' => $option_value, 'label' => $option_name];
            if ($option_value == $selected_value) {
                $option_attributes['selected'] = 'selected';
            }
            array_push($option_elements, new Element('option', $option_attributes));
        }
        return $option_elements;
    }

    private function get_description(?string $description, array $parent_attributes): Element
    {
        # build attributes
        $description_attributes = ['class' => 'description'];
        if (isset($parent_attributes['id'])) {
            $description_attributes['id'] = "{$parent_attributes['id']}-description";
        }

        # if we have a proper description
        if ($description) {
            return new Element('p', $description_attributes, $description);
        }

        # if we have an id
        if (isset($parent_attributes['name'])) {
            $description = new Element('pre', $description_attributes, $parent_attributes['name']);
            return new Element('p', $description_attributes, $description);
        }

        # if we have a name
        if (isset($parent_attributes['id'])) {
            $description = new Element('pre', $description_attributes, $parent_attributes['id']);
            return new Element('p', $description_attributes, $description);
        }
        # if we got nothing
        return new Element('p', $description_attributes, 'hello world');
    }

    public function set_value(mixed $value): FormControlElement
    {
        $this->attributes['value'] = $value;
        return $this;
    }

    public function set_id(string $form_id): FormControlElement
    {
        $this->attributes['id'] = $form_id;
        return $this;
    }
}
