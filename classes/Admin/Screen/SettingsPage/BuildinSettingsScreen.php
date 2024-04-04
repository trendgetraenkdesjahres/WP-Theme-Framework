<?php

namespace WP_Framework\Admin\Screen\SettingsPage;

use WP_Framework\Admin\Screen\AbstractScreen;
use WP_Framework\Admin\Screen\SettingsPage\Content\Field;

/**
 * Class BuildinSettingsScreen
 * Represents a built-in settings screen.
 */
class BuildinSettingsScreen extends PrimarySettingsScreen
{
    /**
     * Constructor for the BuildinSettingsScreen class.
     *
     * @param string $page_subject_singular The singular subject of the page.
     */
    public function __construct(string $page_subject_singular)
    {
        AbstractScreen::__construct(self::$required_capabilty, $page_subject_singular);
    }

    /**
     * Retrieves a sub screen by name.
     *
     * @param string $name The name of the sub screen (custom name or 'general', 'reading', 'writing', 'discussion' or 'media')
     * @return SettingsScreen The sub screen.
     */
    public function get_sub_screen(string $name): SettingsScreen
    {
        if (in_array($name, ['general', 'reading', 'writing', 'discussion', 'media'])) {
            return $this->sub_screens[$name];
        }
        if (!isset($this->sub_screens[$name])) {
            throw new \Error("\$sub_screen '{$name}' does not exist.");
        }
        return $this->sub_screens[$name];
    }

    /**
     * Registers sub screens.
     *
     * @param SettingsScreen ...$sub_screen The sub screens to register.
     * @return static
     */
    public function register_sub_screen(SettingsScreen ...$sub_screen): static
    {
        foreach ($sub_screen as $sub_screen) {
            $this->sub_screens[$sub_screen->name] = $sub_screen;
            add_action($sub_screen->register_hook, $sub_screen->get_register_callback($this->name));
        }
        return $this;
    }

    /**
     * Retrieves the register callback for the screen.
     *
     * @param string|null $parent_name The name of the parent screen.
     * @return callable The register callback.
     */
    public function get_register_callback(?string $parent_name = null): callable
    {
        return function () {
            register_setting($this->name, $this->get_option_name());
        };
    }

    /**
     * Registers a field.
     *
     * @param Field $field The field to register.
     * @param string|null $after_section The name of the section after which to register the field.
     * @return static
     */
    public function register_field(Field $field, ?string $after_section = null): static
    {
        $field->section_name = $after_section ? $after_section : $this->get_last_section_name();

        # if we are in the main build-in
        if ($this->name === 'options-general.php') {
            $this->sub_screens['general']->register_field($field);
            return $this;
        }
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
}
