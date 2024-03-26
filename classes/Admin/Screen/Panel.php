<?php

namespace WP_Framework\Admin\Screen;

use WP_Framework\Admin\Screen\Editor\Editor;
use WP_Framework\Admin\Screen\Table\Table;

class Panel
{
    public string $name;
    public string $singular_name;
    public string $plural_name;
    protected string $required_capabilty;

    protected string $register_hook = 'admin_menu';

    protected string $load_table_hook;
    protected ?Table $table = null;

    protected string $load_editor_hook;
    protected ?Editor $editor = null;

    public function __construct(string $required_capabilty, $name, ?string $plural_name = null)
    {
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

    public function set_table(Table $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function set_editor(Editor $editor): self
    {
        $this->editor = $editor;
        return $this;
    }


    /**
     * Get the menu callback function for this admin panel.
     *
     * @param string $model The model associated with the meta field.
     * @return callable The save callback function.
     */
    public function get_menu_callback(): callable
    {
        return function () {
            $this->table->set_screen_name(
                add_menu_page(
                    page_title: $this->plural_name,
                    menu_title: $this->plural_name,
                    capability: $this->required_capabilty,
                    menu_slug: $this->name,
                    callback: $this->get_table_callback(),
                    icon_url: 'none',
                    position: 30 # if two menu items use the same position attribute, one of the items may be overwritten so that only one item displays!
                )
            );
            $this->table->register_screen_options();

            $this->editor->set_screen_name(
                add_submenu_page(
                    parent_slug: "{$this->name}",
                    page_title: "Create new {$this->singular_name}",
                    menu_title: "Create new {$this->singular_name}",
                    capability: $this->required_capabilty,
                    menu_slug: "new_{$this->name}",
                    callback: $this->get_create_new_callback(),
                    position: 1
                )
            );
        };
    }

    protected function get_table_callback(): callable
    {
        return function () {
            echo "<div class='wrap'>
            <h1 class='wp-heading-inline'>{$this->plural_name}</h1>
            <a href=" . admin_url("admin.php?page={$this->name}") . " class='page-title-action'>Add</a>
            </div>" . $this->table . "<div class='clear'></div>";
        };
    }

    protected function get_create_new_callback(): callable
    {
        return function () {
            echo "<div class='wrap'>
            <h1 class='wp-heading-inline'>Create new {$this->singular_name}</h1>
            </div>" . $this->editor . "<div class='clear'></div>";
        };
    }
}
