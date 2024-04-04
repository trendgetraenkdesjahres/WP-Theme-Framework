<?php

namespace WP_Framework\Model;

use WP_Framework\Database\Database;
use WP_Framework\Database\Table\AbstractTable;
use WP_Framework\Model\Meta\AbstractMeta;

/**
 * Handles an object ('post', 'term', 'user', ...) in WordPress.
 *
 * @abstract
 */
abstract class AbstractModel
{
    /**
     * The internal name of this model.
     *
     * @var string
     */
    public string $name;

    /**
     * The internal name of this model.
     *
     * @var string
     */
    protected string $model_name;

    /**
     * The internal name of this model.
     *
     * @var string
     */
    protected ?string $type_name = null;

    /**
     * Array to store meta fields. Null if the model does not support meta.
     *
     * @var array|null
     */
    protected ?array $meta = null;

    public function set_meta_support(bool $support_meta = true): static
    {
        if ($this->meta === null && $support_meta == true) {
            $this->meta = [];
        }
        if (is_array($this->meta) && $support_meta == false) {
            $this->meta = null;
        }
        return $this;
    }
    /**
     * Get a meta object of this model type.
     *
     * @param string $name The (serialized) name of the meta.
     * @return AbstractMeta The AbstractMeta instance.
     * @throws \Error If the specified meta is not registered.
     */
    public function get_meta(string $name): AbstractMeta
    {
        $this->validate_meta_support();
        if (!isset($this->meta[$name])) {
            throw new \Error("A {$this->name}-meta named '$name' is not registered");
        }
        return $this->meta[$name];
    }

    /**
     * Get all registered meta objects for this model.
     * Sorry for the plural.
     *
     * @return array All the registered meta objects.
     */
    public function get_metas(): array
    {
        $this->validate_meta_support();
        return $this->meta;
    }

    /**
     * Register custom meta fields for this model.
     *
     * @param AbstractMeta $meta The WP_Framework Meta object to register.
     * @return static The modified static instance.
     * @throws \Error If the specified meta is not registered.
     */
    public function register_meta(AbstractMeta $meta): static
    {
        $this->validate_meta_support();
        $meta->set_key($this->model_name);
        register_meta(
            object_type: $this->model_name,
            meta_key: $meta->key,
            args: $this->create_meta_options($meta)
        );
        return $this
            ->add_meta($meta)
            ->hook_meta_actions($meta);
    }

    /**
     * Unregister custom meta fields for this model.
     *
     * @param AbstractMeta $meta The WP_Framework Meta object or a it's key to unregister.
     * @return static The modified AbstractType instance.
     */
    public function unregister_meta(AbstractMeta $meta): static
    {
        $this->validate_meta_support();
        unregister_meta_key($this->model_name, $meta->key);
        return $this
            ->remove_meta($meta)
            ->unhook_meta_actions($meta);
    }

    /**
     * Create options for registering meta fields.
     *
     * @param AbstractMeta $meta The meta object.
     * @return array The meta options.
     */
    protected function create_meta_options(AbstractMeta $meta): array
    {
        if (!isset($meta->default)) {
            $default = $meta->cast_to_data_type('');
        } else {
            $default = $meta->default;
        }
        if ($this->type_name) {
            $meta->options['object_subtype'] = $this->type_name;
        }
        return array_merge($meta->options, [
            'type' => $meta->type,
            'description' => $meta->description,
            'single' => true,
            'default' => $default,
            'show_in_rest', true
        ]);
    }

    /**
     * Hook meta save and edit methods into wordpress actions for the given meta.
     *
     * @param AbstractMeta $meta The meta object.
     * @return static The modified static instance.
     */
    protected function hook_meta_actions(AbstractMeta $meta): static
    {
        # add meta input to 'edit' screens
        foreach ($meta->get_edit_hooks($this->type_name) as $edit_hook) {
            add_action($edit_hook, $meta->get_edit_callback());
        }

        # add save-methods
        foreach ($meta->get_save_hooks($this->type_name) as $save_hook) {
            add_action($save_hook, $meta->get_save_callback($this->name));
        }
        return $this;
    }

    /**
     * Unhook meta save and edit methods actions for the given meta.
     *
     * @param AbstractMeta $meta The meta object.
     * @return static The modified static instance.
     */
    protected function unhook_meta_actions(AbstractMeta $meta): static
    {
        # remove meta input from 'edit' screens
        foreach ($meta->get_edit_hooks($this->type_name) as $edit_hook) {
            remove_action($edit_hook, $meta->get_edit_callback());
        }

        # remove save-methods
        foreach ($meta->get_save_hooks($this->type_name) as $save_hook) {
            remove_action($save_hook, $meta->get_save_callback($this->name));
        }
        return $this;
    }

    /**
     * Add a meta object to the list of registered meta objects.
     *
     * @param AbstractMeta $meta The meta object.
     * @return static The modified static instance.
     */
    protected function add_meta(AbstractMeta $meta): static
    {
        $this->meta[$meta->key] = $meta;
        return $this;
    }

    /**
     * Remove a meta object from the list of registered meta objects.
     *
     * @param string|AbstractMeta $meta The meta object or its key.
     * @return static The modified static instance.
     */
    protected function remove_meta(string|AbstractMeta $meta): static
    {
        if (!is_string($meta)) {
            $meta = $meta->name;
        }
        unset($this->meta[$meta]);
        return $this;
    }

    protected function validate_meta_support(): void
    {
        if ($this->meta === null) {
            throw new \Error("Model '{$this->name}' does not support meta.");
        }
    }


    /**
     * Get the database table name for the model.
     *
     * @return string The database table name.
     */
    abstract public function get_table_name(): string;

    public function get_table(): AbstractTable
    {
        return Database::get_table(
            $this->get_table_name()
        );
    }
}
