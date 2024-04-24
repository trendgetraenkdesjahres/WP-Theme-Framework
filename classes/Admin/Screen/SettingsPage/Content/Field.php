<?php

namespace WP_Framework\Admin\Screen\SettingsPage\Content;

use WP_Framework\Element\Input\FormControlElement;

/**
 * Class Field
 * Represents a field for a settings page.
 */
class Field
{
    /**
     * @var string The name of the field.
     */
    public string $name;

    /**
     * @var string The title of the field.
     */
    public string $title;

    /**
     * @var string The input type of the field.
     */
    protected string $html_input_type;

    /**
     * @var string The description of the field.
     */
    protected string $description;

    /**
     * @var string|null The default value of the field.
     */
    protected ?string $default_value;

    /**
     * @var array The options of the field.
     */
    protected array $options;

    /**
     * @var string The name of the page options.
     */
    public string $page_options_name;

    /**
     * @var string The name of the section.
     */
    public string $section_name;

    /**
     * Field constructor.
     *
     * @param string $title The title of the field.
     * @param string $description The description of the field.
     * @param string $html_input_type The input type of the field.
     * @param string|null $default_value The default value of the field.
     * @param array $options The options of the field.
     */
    public function __construct(string $title, string $description, string $html_input_type, ?string $default_value = null, $options = [])
    {
        $this->name = sanitize_title($title);
        $this->title = $title;
        $this->description = $description;
        $this->html_input_type = $html_input_type;
        $this->default_value = $default_value;
        $this->options = $options;
    }

    /**
     * Get the display callback for the field.
     *
     * @param mixed $value The value of the field.
     * @return callable The display callback.
     */
    public function get_display_callback(mixed $value): callable
    {
        return function () use ($value) {
            echo (string) $this->create_form_control()
                ->set_value($value);
        };
    }

    /**
     * Set the name of the page options.
     *
     * @param string $name The name of the page options.
     * @return static The modified instance.
     */
    public function set_page_options_name(string $name): static
    {
        $this->page_options_name = $name;
        return $this;
    }

    /**
     * Get the register-options for displaying the field.
     *
     * @return array The options for displaying the field.
     */
    public function get_field_options(): array
    {
        return [
            'label_for' => $this->name,
            'class' => 'row'
        ];
    }

    /**
     * Create the form control element for the field.
     *
     * @return FormControlElement The form control element.
     */
    protected function create_form_control()
    {
        return new FormControlElement('input', ['type' => $this->html_input_type, 'id' => $this->name, 'name' => "{$this->page_options_name}[{$this->name}]"], $this->description, $this->options);
    }
}
