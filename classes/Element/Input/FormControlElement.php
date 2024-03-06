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
    /**
     * FormControlElement constructor.
     *
     * @param string $tag_name The tag name for the form control element ('input', 'select', or 'textarea').
     * @param array $attributes The attributes for the form control element.
     * @param string $description The description for the form control element.
     * @param array $options Optional options for <select> elements ['value' => 'name', ...].
     */
    public function __construct(private string $tag_name, private array $attributes, string $description, $options = [])
    {
        # build description tag
        $description_attributes = ['class' => 'description'];
        if (isset($attributes['id'])) {
            $description_attributes['id'] = "{$attributes['id']}-description";
        }
        $description = new Element('p', $description_attributes, $description);

        # create input tags
        switch ($tag_name) {
            case 'input':
                $input = new Element('input', $attributes);
                break;
            case 'textarea':
                $input = new Element('textarea', $attributes, $attributes['value']);
                break;
            case 'select':
                $option_elements = [];
                foreach ($options as $option_value => $option_name) {
                    $option_attributes = ['value' => $option_value];
                    if ($option_value == $attributes['value']) {
                        $option_attributes['selected'] = 'selected';
                    }
                    array_push($option_elements, new Element('option', $option_attributes, $option_name));
                }
                $input = new Element('select', $attributes, ...$option_elements);
                break;

            default:
                throw new \Error("No handling for '$tag_name' defined.");
        }
        parent::__construct($input, $description);
    }
}
