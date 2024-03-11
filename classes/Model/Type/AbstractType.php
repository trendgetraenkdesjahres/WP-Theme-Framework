<?php

namespace WP_Framework\Model\Type;

use WP_Framework\Database\SQLSyntax;
use WP_Framework\Model\AbstractModel;
use WP_Framework\Model\Meta\AbstractMeta;

/**
 * Handles a model type. a model type is a variation of a model.
 * it has the same properties (table columns) as a model, but not the same values, not the same meta and can be displayed differently.
 * for example 'page' and 'attachement' of posttype, 'categorie' and 'tag' of termtype/taxonomy are WordPress build-in models.
 */
abstract class AbstractType extends AbstractModel
{
    /**
     * The Internal name of the type.
     */
    public string $name;

    public function __construct(string $name, public array $props = [])
    {
        if (!SQLSyntax::field_name($name)) {
            throw new \Error("'$name' is not a valid type-name");
        }
        $this->name = $name;
    }

    public function register_meta(AbstractMeta $meta): AbstractType
    {
        #register
        $options = array_merge($meta->options, [
            'type' => $meta->type,
            'description' => $meta->description,
            'object_subtype' => $this->name
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

    public function unregister_meta(AbstractMeta $meta): AbstractType
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
}
