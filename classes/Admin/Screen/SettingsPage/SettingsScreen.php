<?php

namespace WP_Framework\Admin\Screen\SettingsPage;

use WP_Framework\Admin\Screen\AbstractScreen;
use WP_Framework\Admin\Screen\SettingsPage\Content\Field;
use WP_Framework\Admin\Screen\SettingsPage\Content\Section;
use WP_Framework\Admin\Screen\WP_ScreenTrait;

/**
 * Class SettingsScreen
 * Represents a settings screen containing sections and fields.
 */
class SettingsScreen extends AbstractScreen
{
    use WP_ScreenTrait;

    /**
     * @var array An array of sections associated with this settings screen.
     **/
    protected array $sections = [];

    /**
     * @var array An array of fields associated with this settings screen.
     **/
    protected array $fields = [];

    /**
     * @var array|null An array containing current values for fields, if available.
     **/
    protected ?array $current_values = null;

    /**
     * @var string The required capability to access this settings screen.
     **/
    protected static string $required_capabilty = 'manage_options';

    /**
     * Constructor for the SettingsScreen class.
     *
     * @param string $page_subject_singular The singular subject of the settings page.
     * @param string|null $page_subject_plural The plural subject of the settings page.
     */
    public function __construct(string $page_subject_singular, ?string $page_subject_plural = null)
    {
        parent::__construct(self::$required_capabilty, $page_subject_singular, $page_subject_plural);
        // the silent 'default' section
        $this->register_section(new Section(''));
    }

    /**
     * Registers content (sections and fields) for this settings screen.
     *
     * @param string|Section|Field ...$content The content to register.
     * @return static
     */
    public function register_content(string|Section|Field ...$content): self
    {
        foreach ($content as $content) {
            if (is_string($content)) {
                $title = '';
                if (strlen($content) < 25) {
                    $title = $content;
                    $content = '';
                }
                $this->register_section(new Section($title, $content));
                continue;
            }
            if ($content instanceof Section) {
                $this->register_section($content);
                continue;
            }
            $this->register_field($content);
        }
        return $this;
    }

    /**
     * Registers a section for this settings screen.
     *
     * @param Section $section The section to register.
     * @return static
     */
    public function register_section(Section $section): static
    {
        $this->sections[$section->name] = $section;
        add_action('admin_init', function () use ($section) {
            add_settings_section(
                id: $section->name,
                title: $section->get_title(),
                callback: $section->get_display_callback(),
                page: $this->name,
                args: $section->get_section_options()
            );
        });
        return $this;
    }

    /**
     * Unregisters a section from this settings screen.
     *
     * @param Section $section The section to unregister.
     * @return static
     */
    public function unregister_section(Section $section): static
    {
        global $wp_settings_sections;
        unset($wp_settings_sections[$this->name][$section->name]);
        unset($this->sections[$section->name]);
        return $this;
    }

    /**
     * Registers a field for this settings screen.
     *
     * @param Field $field The field to register.
     * @param string|null $after_section The section after which to place the field.
     * @return static
     */
    public function register_field(Field $field, ?string $after_section = null): static
    {
        $field->section_name = $after_section ? $after_section : $this->get_last_section_name();
        $field->set_page_options_name($this->get_option_name());
        $this->fields[$field->name] = $field;
        add_action('admin_init', function () use ($field) {
            add_settings_field(
                id: $field->name,
                title: $field->title,
                callback: $field->get_display_callback($this->get_current_value($field)),
                page: $this->name,
                section: $field->section_name,
                args: $field->get_field_options()
            );
        });
        return $this;
    }

    /**
     * Retrieves the current value for a given field.
     *
     * @param string|Field $field The name of the field or the field object itself.
     * @return mixed The current value of the field.
     * @throws \Error If the specified field does not exist.
     */
    public function get_current_value(string|Field $field): mixed
    {
        if ($field instanceof Field) {
            $field = $field->name;
        }
        if (!isset($this->fields[$field])) {
            throw new \Error("Field '$field' does not exist.");
        }
        $this->set_current_values();
        return isset($this->current_values[$field]) ? $this->current_values[$field] : '';
    }


    /**
     * Sets the current values for fields.
     *
     * @return static
     */
    protected function set_current_values(): static
    {
        if ($this->current_values === null) {
            $current_values = get_option($this->get_option_name());
            if (!is_array($current_values)) {
                return $this;
            }
            $this->current_values = $current_values;
        }
        return $this;
    }

    /**
     * Retrieves a section by name.
     *
     * @param string $section The name of the section to retrieve.
     * @return Section The section object.
     * @throws \Error If the specified section does not exist.
     */
    public function get_section(string $section): Section
    {
        if (!isset($this->sections[$section])) {
            throw new \Error("Section '$section' does not exist.");
        }
        return $this->sections[$section];
    }

    /**
     * Retrieves the register callback function for this settings screen.
     *
     * @param string|null $parent_name The name of the parent screen, if any.
     * @return callable The register callback function.
     */
    public function get_register_callback(?string $parent_name = null): callable
    {
        return function () use ($parent_name) {
            register_setting($this->name, $this->get_option_name());
            add_submenu_page(
                parent_slug: $parent_name,
                page_title: $this->singular_name,
                menu_title: $this->singular_name,
                capability: self::$required_capabilty,
                menu_slug: "{$parent_name}_{$this->name}",
                callback: $this->get_display_callback(),
                position: null
            );
        };
    }

    /**
     * Retrieves the display callback function for this settings screen.
     *
     * @return callable The display callback function.
     */
    public function get_display_callback(): callable
    {
        return function () {
            if (isset($_GET['settings-updated'])) {
                add_settings_error($this->name, $this->name, "{$this->plural_name} Saved", 'updated');
            }
            settings_errors($this->name);
            if (!current_user_can(self::$required_capabilty)) {
                return;
            }
            echo "<div class='wrap'><h1>" . esc_html($this->singular_name) . "</h1><form action='options.php' method='post'>";
            settings_fields($this->name);
            do_settings_sections($this->name);
            submit_button('Save');
            echo "</form></div>";
        };
    }

    /**
     * Retrieves the name of the last registred section.
     *
     * @return string The name of the last section.
     */
    protected function get_last_section_name(): string
    {
        return $this->sections ? end($this->sections)->name : 'default';
    }

    /**
     * Retrieves the option name for this settings screen.
     *
     * @return string The option name.
     */
    public function get_option_name(): string
    {
        return $this->name;
    }
}
