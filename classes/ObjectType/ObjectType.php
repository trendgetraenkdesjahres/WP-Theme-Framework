<?php

namespace WP_ThemeFramework\ObjectType;

use WP_ThemeFramework\Meta\MetaInterface;
use WP_ThemeFramework\Utils\JsonFile;

/**
 * Interface for object type (post, term, user, comment, ...) fields in WordPress.
 */
interface ObjectTypeInterface
{
    /**
     * Register a custom object type with WordPress.
     *
     * @return ObjectTypeInterface The modified PostType instance.
     */
    public function register(): ObjectTypeInterface;

    /**
     * Unregister the custom object type.
     * Cannot be used to unregister built-in post types, use PostType->hide() instead.
     *
     * @return ObjectTypeInterface The modified PostType instance.
     */
    public function unregister(): ObjectTypeInterface;

    /**
     * Check if the custom object type is registered.
     *
     * @return bool True if the post type is registered, false otherwise.
     */
    public function is_registered(): bool;

    /**
     * Go get the thing
     *
     * @return string
     */
    public function get_object_type(): string;
}

/**
 * Handles a object type ('page' of posttype, 'categorie' of termtype/taxonomy, ...) in WordPress.
 */
abstract class ObjectType
{
    public function __construct(public string $name, public array $props = [])
    {
    }

    public function get_object_type(): string
    {
        return 'abstract ( :-0 ) abstract';
    }

    public static function create_from_json(string $path): ObjectTypeInterface
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
     * @return ObjectType The modified ObjectType instance.
     */
    public function register_meta(MetaInterface $meta): ObjectType
    {
        $meta->register($this->name);
        return $this;
    }

    /**
     * Unregister custom meta fields for this post type.
     *
     * @param string|MetaInterface $meta The WP_Framework Meta object or a string (builtin Meta) to unregister.
     * @return ObjectType The modified ObjectType instance.
     */
    public function unregister_meta(string|MetaInterface $meta): ObjectType
    {
        if (is_string($meta)) {
            unregister_meta_key($this->get_object_type(), $meta, $this->name);
            return $this;
        }
        # TODO not implemented yet
        /* $meta->unregister(); */
        return $this;
    }
}
