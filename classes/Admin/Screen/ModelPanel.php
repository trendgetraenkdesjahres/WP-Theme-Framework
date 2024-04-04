<?php

namespace WP_Framework\Admin\Screen;

use WP_Framework\Admin\Screen\Table\ModelTable;
use WP_Framework\Admin\Screen\Editor\ModelEditor;
use WP_Framework\Model\CustomModel;

/**
 * Class ModelPanel
 * Represents a screen panel for managing a custom model.
 */
class ModelPanel extends AbstractScreen
{
    /**
     * @var string The hook for loading the table.
     */
    protected string $load_table_hook;

    /**
     * @var ModelTable|null The table associated with this panel.
     */
    protected ?ModelTable $table = null;

    /**
     * @var string The hook for loading the editor.
     */
    protected string $load_editor_hook;

    /**
     * @var ModelEditor|null The editor associated with this panel.
     */
    protected ?ModelEditor $editor = null;

    /**
     * Constructor for the ModelPanel class.
     *
     * @param CustomModel $model The custom model associated with the panel.
     */
    public function __construct(CustomModel $model)
    {
        # Register actions to load table and editor
        add_action($this->register_hook, function () use ($model) {
            $this->table = new ModelTable($model);
            $this->editor = new ModelEditor($model);
        });

        # Get the required capability for the model
        $capability = $model->get_capability_attribute("edit_{$model->name}s", false);

        parent::__construct(
            required_capabilty: $capability,
            title: $model->singular_name,
            plural_title: $model->plural_name,
        );
    }

    /**
     * Set the table associated with this panel.
     *
     * @param ModelTable $table The table to set.
     * @return self
     */
    public function set_table(ModelTable $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Set the editor associated with this panel.
     *
     * @param ModelEditor $editor The editor to set.
     * @return self
     */
    public function set_editor(ModelEditor $editor): self
    {
        $this->editor = $editor;
        return $this;
    }

    /**
     * Get the menu callback function for registering this admin panel.
     *
     * @param string|null $parent_name The name of the parent menu item (optional).
     * @return callable The menu callback function.
     */
    public function get_register_callback(?string $parent_name = null): callable
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

    /**
     * Get the callback function for rendering the table.
     *
     * @return callable The callback function for rendering the table.
     */
    protected function get_table_callback(): callable
    {
        return function () {
            echo "<div class='wrap'>
            <h1 class='wp-heading-inline'>{$this->plural_name}</h1>
            <a href=" . admin_url("admin.php?page={$this->name}") . " class='page-title-action'>Add</a>
            </div>" . $this->table . "<div class='clear'></div>";
        };
    }

    /**
     * Get the callback function for rendering the 'create new' form.
     *
     * @return callable The callback function for rendering the create new form.
     */
    protected function get_create_new_callback(): callable
    {
        return function () {
            echo "<div class='wrap'>
            <h1 class='wp-heading-inline'>Create new {$this->singular_name}</h1>
            </div>" . $this->editor . "<div class='clear'></div>";
        };
    }
}
