<?php

namespace WP_Framework\Model;

use WP_Framework\AdminPanel\Table\AbstractTable;
use WP_Framework\Model\Meta\AbstractMeta;

/**
 * Handles a object ('post', 'term', 'user', ...) in WordPress.
 */
abstract class AbstractModel
{
    /**
     * The internal name of this model.
     */
    public string $name;

    /**
     * Array to store meta fields in. Null if model does not support meta.
     */
    protected ?array $meta = null;

    /**
     * The optional models custom admin-panel table representation.
     */
    protected ?AbstractTable $panel_table = null;

    /**
     * Register custom meta fields for this model type.
     *
     * @param AbstractMeta $meta The WP_Framework Meta object to register.
     * @return AbstractModel The modified AbstractType instance.
     */
    public function register_meta(AbstractMeta $meta): AbstractModel
    {
        #register
        $options = array_merge($meta->options, [
            'type' => $meta->type,
            'description' => $meta->description
        ]);
        register_meta($this->name, $meta->key, $options);

        # add meta input to 'edit' screens
        foreach ($meta->edit_hooks as $edit_hook) {
            add_action($edit_hook, $meta->get_edit_callback());
        }

        # add save-methods
        foreach ($meta->save_hooks as $save_hook) {
            add_action($save_hook, $meta->get_save_callback());
        }
        $this->meta[$meta->key] = $meta;
        return $this;
    }

    /**
     * Unregister custom meta fields for this model type.
     *
     * @param string|AbstractMeta $meta The WP_Framework Meta object or a string (builtin Meta) to unregister.
     * @return AbstractModel The modified AbstractType instance.
     */
    public function unregister_meta(AbstractMeta $meta): AbstractModel
    {
        # unregister
        unregister_meta_key($this->name, $meta->key);

        # remove meta input from 'edit' screens
        foreach ($meta->edit_hooks as $edit_hook) {
            remove_action($edit_hook, $meta->get_edit_callback());
        }

        # remove save-methods
        foreach ($meta->save_hooks as $save_hook) {
            remove_action($save_hook, $meta->get_save_callback());
        }
        unset($this->meta[$meta->key]);
        return $this;
    }

    /**
     * Get a meta object of this model type.
     *
     * @param string $name the (serialized) name of the meta.
     * @return AbstractMeta The AbstractMeta instance.
     */
    public function get_meta(string $name): AbstractMeta
    {
        if (!isset($this->meta[$name])) {
            throw new \Error("A {$this->name}-meta named '$name' is not registered");
        }
        return $this->meta[$name];
    }
}
