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
use WP_Framework\Model\CustomModel;
use WP_Framework\Model\Instance\CustomInstance;
use WP_Framework\Model\Property\Property;

/**
 * Class ModelEditor
 * Represents an editor screen for managing instances of a custom model.
 */
class ModelEditor
{
    use WP_ScreenTrait;
    /**
     * @var array $hidden_forms An array to hold hidden form elements.
     */
    protected array $hidden_forms = [];

    /**
     * @var array $visible_forms An array to hold visible form elements.
     */
    protected array $visible_forms = [];

    /**
     * @var string $name The name of the editor.
     */
    public string $name;

    /**
     * @var string $action The action URL for the editor form.
     */
    protected string $action;

    /**
     * @var CustomInstance $instance The custom instance being edited, if any.
     */
    protected CustomInstance $instance;

    /**
     * @var bool $meta_sidebar A flag indicating whether a meta sidebar should be displayed.
     */
    protected bool $meta_sidebar;

    /**
     * Constructor for the ModelEditor class.
     *
     * @param CustomModel $model The custom model associated with the editor.
     * @param CustomInstance|null $instance The custom instance being edited, if any.
     */
    public function __construct(CustomModel $model, ?CustomInstance $instance = null)
    {
        $this->name = $model->name;
        $this->action = 'post.php';
        $this->instance = $instance;
        $this->meta_sidebar = $model->meta === false ? false : true;
        $this->add_property_form(...$model->get_properties());
    }

    /**
     * Registers visible form fields.
     *
     * @param Element ...$element The form fields to register.
     * @return static
     */
    public function register_field(Element ...$element): static
    {
        foreach ($element as $element) {
            array_push($this->visible_forms, $element);
        }
        return $this;
    }

    /**
     * Registers hidden form fields.
     *
     * @param Element ...$element The hidden form fields to register.
     * @return static
     */
    public function register_hidden_field(Element ...$element): static
    {
        foreach ($element as $element) {
            array_push($this->hidden_forms, $element);
        }
        return $this;
    }

    /**
     * Converts the ModelEditor object to its string representation.
     *
     * @return string The string representation of the ModelEditor object.
     */
    public function __toString(): string
    {
        return (string) $this->get_wrapped_fields();
    }

    /**
     * Retrieves the submit box element for the ModelEditor.
     *
     * @return FormControlElement The submit box element.
     */
    public function get_submit_box(): FormControlElement
    {
        $header = Element::from_string(
            '<div class="postbox-header"><h2 class="hndle ui-sortable-handle">Publish</h2></div>'
        );
        return new FormControlElement('input', ['type' => 'submit', 'value' => 'submit'], 'Submit');
    }

    /**
     * Adds property forms to the model editor screen.
     *
     * @param Property ...$property The properties to add forms for.
     * @return static
     */
    protected function add_property_form(Property ...$property): static
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
        return $this;
    }

    /**
     * Adds a form element for the title property.
     *
     * @param string|null $value The value of the title property, if available.
     * @return static
     */
    protected function add_title_form(?string $value): static
    {
        array_unshift(
            $this->visible_forms,
            FormControlElement::create_input_text("{$this->name}_title", $value)
        );
        return $this;
    }

    /**
     * Adds a form element for the status property.
     *
     * @param string|null $value The value of the status property, if available.
     * @return static
     */
    protected function add_status_form(?string $value): static
    {
        array_push(
            $this->hidden_forms,
            FormControlElement::create_input_hidden("{$this->name}_status", $value)
        );
        return $this;
    }

    /**
     * Adds a form element for the type property.
     *
     * @param string|null $value The value of the type property, if available.
     * @return static
     */
    protected function add_type_form(?string $value): static
    {
        array_push(
            $this->hidden_forms,
            FormControlElement::create_input_hidden("{$this->name}_type", $value)
        );
        return $this;
    }

    /**
     * Gets the wrapped form fields.
     *
     * @return Element The wrapped form fields.
     */
    private function get_wrapped_fields(): Element
    {
        $wrapper = new Element(
            'form',
            [
                'class' => 'fw_editor',
                'name' => 'editor',
                'action' => 'admin.php',
                'id' => 'post'
            ]
        );
        $wrapper->append_child(...$this->hidden_forms)->append_child(...$this->visible_forms);
        if ($this->meta_sidebar) {
            $meta_box_container = new MetaBoxContainer($this->name, $this->instance);
            $wrapper->append_child($meta_box_container);
        }
        $wrapper->append_child($this->get_submit_box());
        return $wrapper;
    }
}
