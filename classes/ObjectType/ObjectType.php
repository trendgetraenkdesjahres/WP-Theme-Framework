<?php

namespace WP_ThemeFramework\ObjectType;

use WP_ThemeFramework\Meta\Meta;

/**
 * Interface for object type (post, term, user, comment, ...) fields in WordPress.
 */
interface ObjectTypeInterface
{
    /**
     * Register the object type (post-type, term-taxonomy, user, comment, ...).
     *
     * @return ObjectTypeInterface The registered ObjectType instance.
     */
    public function register(): ObjectTypeInterface;

    /**
     * Unregister the object type (post, term, user, comment, ...).
     * Cannot be used to unregister built-in post types, use PostType->hide() instead.
     *
     * @return ObjectTypeInterface The modified PostType instance.
     */
    public function unregister(): ObjectTypeInterface;
}

/**
 * Handles a object type (post, term, user, comment, ...) in WordPress.
 */
abstract class ObjectType
{
    public function __construct(public string $name, public array $props = [])
    {
    }

    /**
     * Register custom meta fields for this post type.
     *
     * @param PostMeta $meta Optional. The meta field object to register.
     * @return PostType The modified PostType instance.
     */
    public function register_meta(Meta $meta): ObjectType
    {
        $meta->register($this->name);
        return $this;
    }

    /**
     * Unregister custom meta fields for this post type.
     *
     * @param PostMeta $meta Optional. The meta field object to register.
     * @return PostType The modified PostType instance.
     */
    public function unregister_meta(string|Meta $meta): PostType
    {
        if (is_string($meta)) {
            unregister_meta_key('post', $meta_key, $post_type);
            return $this;
        }
        $meta->unregister();
        return $this;
    }

    /**
     * Check if the custom post type is registered.
     *
     * @return bool True if the post type is registered, false otherwise.
     */
    public function is_registered(): bool
    {
        return post_type_exists($this->name);
    }
}
