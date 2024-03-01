<?php

namespace WP_ThemeFramework\ObjectType;

use WP_ThemeFramework\Meta\PostMeta;

/**
 * Handles a (custom) post type in WordPress.
 */
class PostType extends ObjectType implements ObjectTypeInterface
{

    /**
     * Register the custom post type with WordPress.
     *
     * @return PostType The modified PostType instance.
     */
    public function register(): PostType
    {
        register_post_type(
            post_type: $this->name,
            args: $this->props
        );
        return $this;
    }

    /**
     * Register custom meta fields for this post type.
     *
     * @param PostMeta $meta The meta field object to register.
     * @return PostType The modified PostType instance.
     */
    public function register_meta(PostMeta $meta): PostType
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
    public function unregister_meta(string|PostMeta $meta): PostType
    {
        if (is_string($meta)) {
            unregister_post_meta($this->name, $meta);
            return $this;
        }
        $meta->unregister();
        return $this;
    }

    /**
     * Hide the custom post type from the UI and search results.
     *
     * @return PostType The modified PostType instance.
     */
    public function hide(): PostType
    {
        add_filter("register_{$this->name}_post_type_args", function () {
            return [
                'public' => false,
                'show_ui' => false,
                'show_in_menu' => false,
                'show_in_admin_bar' => false,
                'show_in_nav_menus' => false,
                'can_export' => false,
                'has_archive' => false,
                'exclude_from_search' => true,
                'publicly_queryable' => false,
                'show_in_rest' => false
            ];
        });
        return $this;
    }

    /**
     * Unregister the custom post type.
     * Cannot be used to unregister built-in post types, use PostType->hide() instead.
     *
     * @return PostType The modified PostType instance.
     */
    public function unregister(): PostType
    {
        unregister_post_type($this->name);
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
