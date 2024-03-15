<?php

namespace WP_Framework\Admin\Panel;

abstract class AbstractPanel
{
    public string $name;
    public string $singular_name;
    public string $plural_name;
    protected string $required_capabilty;

    /**
     * @var string The hook used to add this panel to the menu.
     */
    protected string $menu_hook = 'admin_menu';

    public function __construct(string $required_capabilty, $name, ?string $plural_name = null) {
        $this->required_capabilty = $required_capabilty;
        $this->set_names($name, $plural_name);
    }

    /**
     * Set the names properties
     *
     * @param string      $name        The name of the data model.
     * @param string|null $plural_name The plural form of the name (optional).
     *
     * @return self The modified instance.
     */
    private function set_names(string $name, ?string $plural_name = null): self
    {
        $this->name = sanitize_key($name);
        $this->singular_name = $name;
        $this->plural_name = $plural_name ? $plural_name : $name . 's';
        return $this;
    }

    public function get_menu_hook(): string {
        return $this->menu_hook;
    }

    abstract public function get_create_new_screen(): string;

    abstract public function get_table_screen(): string;

    /**
     * Get the menu callback function for this admin panel.
     *
     * @param string $model The model associated with the meta field.
     * @return callable The save callback function.
     */
    public function get_menu_callback(): callable
    {
        return function() {
            add_menu_page(
                page_title: $this->plural_name,
                menu_title: $this->plural_name,
                capability: $this->required_capabilty,
                menu_slug: $this->name,
                callback: $this->echo_table_callback(),
                icon_url: 'none',
                position: 30 # if two menu items use the same position attribute, one of the items may be overwritten so that only one item displays!
            );
            add_submenu_page(
                parent_slug: "{$this->name}",
                page_title: "Create new {$this->singular_name}",
                menu_title: "Create new {$this->singular_name}",
                capability: $this->required_capabilty,
                menu_slug: "new_{$this->name}",
                callback: $this->echo_create_new_callback(),
                position: 1
            );
        };
    }

    protected function echo_table_callback(): callable
    {
        return function () {
            echo "<div class='wrap'>
            <h1 class='wp-heading-inline'>{$this->plural_name}</h1>
            <a href=" . admin_url("admin.php?page={$this->name}") . " class='page-title-action'>Add</a>
            </div>" . $this->get_table_screen() . "<div class='clear'></div>";
        };
    }

    protected function echo_create_new_callback(): callable
    {
        return function () {
            echo "<div class='wrap'>
            <h1 class='wp-heading-inline'>Create new {$this->singular_name}</h1>
            </div>" . $this->get_create_new_screen() . "<div class='clear'></div>";
        };
    }
}
