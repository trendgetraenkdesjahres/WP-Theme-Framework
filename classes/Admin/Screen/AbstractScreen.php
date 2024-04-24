<?php

namespace WP_Framework\Admin\Screen;

/**
 * Class AbstractScreen
 * Represents an screen in the admin area.
 */
abstract class AbstractScreen
{
    /**
     * @var string The internal name of the screen.
     **/
    public string $name;

    /**
     * @var string The singular name of the screen.
     **/
    public string $singular_name;

    /**
     * @var string|null The plural name of the screen (optional).
     **/
    public ?string $plural_name;

    /**
     * @var string The required capability to access the screen.
     **/
    protected static string $required_capabilty;

    /**
     * @var string The hook name for registering the screen (default is 'admin_menu').
     **/
    protected string $register_hook = 'admin_menu';
    /**
     * Constructs an AbstractScreen instance.
     *
     * @param string $required_capabilty The required capability for accessing this screen.
     * @param string $title The name of the screen.
     * @param string|null $plural_title The plural form of the name (optional).
     */
    public function __construct(string $required_capabilty, string $title, ?string $plural_title = null)
    {
        static::$required_capabilty = $required_capabilty;
        $this->set_names($title, $plural_title);
    }

    /**
     * Set the names properties.
     *
     * @param string $name The name of the data model.
     * @param string|null $plural_name The plural form of the name (optional).
     * @return self The modified instance.
     */
    private function set_names(string $name, ?string $plural_name = null): self
    {
        $this->name = sanitize_title($name);
        $this->singular_name = $name;
        $this->plural_name = $plural_name ? $plural_name : $name . 's';
        return $this;
    }

    /**
     * Get the callback function for registering the screen.
     *
     * @param string|null $parent_name The parent screen name (optional).
     * @return callable The callback function for registering the screen.
     */
    abstract public function get_register_callback(?string $parent_name = null): callable;
}
