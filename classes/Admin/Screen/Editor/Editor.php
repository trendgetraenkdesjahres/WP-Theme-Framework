<?php

namespace WP_Framework\Admin\Screen\Editor;

/*
- macht die abstraktion vom editor irgendwo sinn? gibt es irgendwann faelle, wo ich sie ohne mode verwenden werde
- die  model implementation sollte eig im model-namespace liegen i guess
- ein ctroller muss her..
 */

use WP_Framework\Admin\Screen\WP_ScreenTrait;
use WP_Framework\Element\Element;
use WP_Framework\Element\Input\FormControlElement;
use WP_Framework\Element\Input\MetaBoxContainer;
use WP_Framework\Model\Instance\CustomInstance;
use WP_Framework\Model\Property\Property;

class Editor
{
    use WP_ScreenTrait;
    protected array $hidden_forms = [];
    protected array $visible_forms = [];

    public function __construct(public string $name, public string $action, public ?CustomInstance $instance = null, private bool $meta_sidebar = false)
    {
    }

    public function add_property_form(Property ...$property)
    {
        foreach ($property as $property) {
            # if we are editing... and not creating smt new
            $value = null;
            if ($this->instance) {
                $value = $this->instance->{$property->key};
            }

            # special properties need special forms
            if ($property->key == 'type') {
                $this->add_type_form($value);
                continue;
            }
            if ($property->key == 'status') {
                $this->add_status_form($value);
                continue;
            }
            if ($property->key == 'title') {
                $this->add_title_form($value);
                continue;
            }

            # default
            array_push($this->visible_forms, $property->get_form_control($value));
        }
    }

    protected function add_title_form(?string $value): self
    {
        array_unshift(
            $this->visible_forms,
            FormControlElement::create_input_text("{$this->name}_title", $value)
        );
        return $this;
    }

    protected function add_status_form(?string $value): self
    {
        array_push(
            $this->hidden_forms,
            FormControlElement::create_input_hidden("{$this->name}_status", $value)
        );
        return $this;
    }

    protected function add_type_form(?string $value): self
    {
        array_push(
            $this->hidden_forms,
            FormControlElement::create_input_hidden("{$this->name}_type", $value)
        );
        return $this;
    }

    public function register_field(Element ...$element): Editor
    {
        foreach ($element as $element) {
            array_push($this->visible_forms, $element);
        }
        return $this;
    }

    public function register_hidden_field(Element ...$element): Editor
    {
        foreach ($element as $element) {
            array_push($this->hidden_forms, $element);
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
                'name' => 'editor',
                'action' => 'admin.php',
                'id' => 'post'
            ]
        );
        $wrapped->append_child(...$this->hidden_forms)->append_child(...$this->visible_forms);
        if ($this->meta_sidebar) {
            $meta_box_container = new MetaBoxContainer($this->name, $this->instance);
            $wrapped->append_child($meta_box_container);
        }
        $wrapped->append_child($this->get_submit_box());
        return $wrapped;
    }

    public function __toString(): string
    {
        return (string) $this->get_wrapped_fields();
    }

    public function get_submit_box()
    {
        $header = Element::from_string(
            '<div class="postbox-header"><h2 class="hndle ui-sortable-handle">Publish</h2></div>'
        );
        return new FormControlElement('input', ['type' => 'submit', 'value' => 'submit'], 'Submit');
    }
}
