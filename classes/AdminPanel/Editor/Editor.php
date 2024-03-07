<?php

namespace WP_Framework\AdminPanel\Editor;

use WP_Framework\Element\Element;
use WP_Framework\Element\Input\FormControlElement;
use WP_Framework\Element\Input\MetaBoxContainer;
use WP_Framework\Model\Property\Property;

class Editor
{
    protected array $hidden_fields = [];
    protected array $fields = [];

    public function __construct(public string $name, public string $action, public ?object $object = null, private bool $meta_sidebar = false)
    {
    }

    public function add_property(Property ...$property)
    {
        foreach ($property as $property) {
            $value = null;
            if ($this->object) {
                $value = $property->get_value($this->object->id);
            }
            if ($property->key == 'type') {
                array_push($this->hidden_fields, new FormControlElement('input', [
                    'type' => 'hidden',
                    'name' => "{$this->name}_type",
                    'value' => $value
                ]));
                continue;
            }

            if ($property->key == 'status') {
                array_push($this->hidden_fields, new FormControlElement('input', [
                    'type' => 'hidden',
                    'name' => "{$this->name}_status",
                    'value' => $value
                ]));
                continue;
            }
            if ($property->key == 'title') {
                array_unshift($this->fields, new FormControlElement('input', [
                    'type' => 'text',
                    'name' => "{$this->name}_title",
                    'value' => $value,
                    'id' => 'title',
                    'placeholder' => 'Insert Title'
                ]));
                continue;
            }
            array_push($this->fields, $property->get_form_control_element($value));
        }
    }

    public function register_field(Element ...$element): Editor
    {
        foreach ($element as $element) {
            array_push($this->fields, $element);
        }
        return $this;
    }

    public function register_hidden_field(Element ...$element): Editor
    {
        foreach ($element as $element) {
            array_push($this->hidden_fields, $element);
        }
        return $this;
    }

    private function get_wrapped_fields(): Element
    {
        # final wrapping
        $wrapped = new Element(
            'form',
            [
                'class' => 'fw_editor',
                'name' => 'post',
                'action' => 'post.php',
                'id' => 'post'
            ]
        );
        $wrapped->append_child(...$this->hidden_fields)->append_child(...$this->fields);
        if ($this->meta_sidebar) {
            $meta_box_container = new MetaBoxContainer($this->name, $this->object);
            $wrapped->append_child($meta_box_container);
        }
        return $wrapped;
    }

    public function __toString(): string
    {
        return (string) $this->get_wrapped_fields();
    }
}
