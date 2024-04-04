<?php

namespace WP_Framework\Admin\Screen\SettingsPage;

/**
 * Class PrimarySettingsScreen
 * Represents the primary settings screen for managing sub-screens.
 */
class PrimarySettingsScreen extends SettingsScreen
{
    /**
     * @var array An array of sub-screens associated with this primary screen.
     **/
    protected array $sub_screens = [];

    /**
     * Retrieves a sub-screen by name.
     *
     * @param string $name The name of the sub-screen to retrieve.
     * @return SettingsScreen The sub-screen.
     * @throws \Error If the specified sub-screen does not exist.
     */
    public function get_sub_screen(string $name): SettingsScreen
    {
        if (!isset($this->sub_screens[$name])) {
            throw new \Error("\$sub_screen '{$name}' does not exist.");
        }
        return $this->sub_screens[$name];
    }

    /**
     * Registers sub-screens for this primary settings screen.
     *
     * @param SettingsScreen ...$sub_screen The sub-screens to register.
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
     * Unregisters a sub-screen from this primary settings screen.
     *
     * @param SettingsScreen $sub_screen The sub-screen to unregister.
     * @return static
     */
    public function unregister_sub_screen(SettingsScreen $sub_screen): static
    {
        unset($this->sub_screens[$sub_screen->name]);
        remove_submenu_page($this->name, "{$this->name}_{$sub_screen->name}");
        return $this;
    }

    /**
     * Retrieves the register callback function for this primary settings screen.
     *
     * @param string|null $parent_name The name of the parent screen, if any.
     * @return callable The register callback function.
     */
    public function get_register_callback(?string $parent_name = null): callable
    {
        return function () {
            register_setting($this->name, $this->get_option_name());
            add_menu_page(
                page_title: $this->singular_name,
                menu_title: $this->singular_name,
                capability: self::$required_capabilty,
                menu_slug: $this->name,
                callback: $this->get_display_callback(),
                icon_url: '',
                position: null
            );
        };
    }
}
