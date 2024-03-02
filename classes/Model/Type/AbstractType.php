<?php

namespace WP_Framework\Model\Type;

use WP_Framework\Model\Type\Meta\MetaInterface;
use WP_Framework\Utils\JsonFile;

/**
 * Interface for object type (post, term, user, comment, ...) fields in WordPress.
 */
interface TypeInterface
{
    /**
     * Register a custom object type with WordPress.
     *
     * @return TypeInterface The modified Type instance.
     */
    public function register(): TypeInterface;

    /**
     * Unregister the custom object type.
     * Cannot be used to unregister built-in post types, use PostType->hide() instead.
     *
     * @return TypeInterface The modified PostType instance.
     */
    public function unregister(): TypeInterface;

    /**
     * Check if the custom object type is registered.
     *
     * @return bool True if the post type is registered, false otherwise.
     */
    public function is_registered(): bool;

    /**
     * Create instance from json data
     *
     * @return TypeInterface The Instace.
     */
    public static function create_from_json(string $path): TypeInterface;
}

/**
 * Handles a object type ('page' of posttype, 'categorie' of termtype/taxonomy, ...) in WordPress.
 */
abstract class AbstractType
{
    protected string $model_name = 'abstract ( :-0 ) abstract';
    /**
     * Tag which is used for the hook in the "add_action" function to register this Type.
     */
    protected string $registration_tag = 'init';

    public function __construct(public string $name, public array $props = [])
    {
    }

    public static function create_from_json(string $path): TypeInterface
    {
        $name = basename($path, '.json');
        $class = get_called_class();
        return new $class(
            name: $name,
            props: JsonFile::to_array($path)
        );
    }

    /**
     * Register custom meta fields for this object type.
     *
     * @param MetaInterface $meta The WP_Framework Meta object to register.
     * @return AbstractType The modified AbstractType instance.
     */
    public function register_meta(MetaInterface $meta): AbstractType
    {
        $meta->register($this->name);
        return $this;
    }

    /**
     * Unregister custom meta fields for this post type.
     *
     * @param string|MetaInterface $meta The WP_Framework Meta object or a string (builtin Meta) to unregister.
     * @return AbstractType The modified AbstractType instance.
     */
    public function unregister_meta(string|MetaInterface $meta): AbstractType
    {
        if (is_string($meta)) {
            unregister_meta_key($this->model_name, $meta, $this->name);
            return $this;
        }
        $meta->unregister($this->model_name);
        return $this;
    }
}
