<?php

namespace WP_ThemeFramework\ObjectType;


/**
 * Handles a (custom) taxonomy (aka a type of term) in WordPress.
 */
class Taxonomy extends ObjectType implements ObjectTypeInterface
{
    public function get_object_type(): string
    {
        return 'term';
    }

    /**
     * Register a custom taxonomy with WordPress.
     *
     * @return Taxonomy The modified PostType instance.
     */
    public function register(): Taxonomy
    {
        register_taxonomy(
            taxonomy: $this->name,
            object_type: $this->props['object_type'],
            args: $this->props
        );
        return $this;
    }

    /**
     * Unregister the custom post type.
     * Cannot be used to unregister built-in post types, use PostType->hide() instead.
     *
     * @return PostType The modified PostType instance.
     */
    public function unregister(): Taxonomy
    {
        unregister_taxonomy($this->name);
        return $this;
    }

    /**
     * Check if the custom post type is registered.
     *
     * @return bool True if the post type is registered, false otherwise.
     */
    public function is_registered(): bool
    {
        return taxonomy_exists($this->name);
    }
}
