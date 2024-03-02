<?php

namespace WP_Framework\Model\Type;

/**
 * Handles a (custom) post type in WordPress.
 */
class PostType extends AbstractType implements TypeInterface
{
    public function get_object_type(): string
    {
        return 'post';
    }

    /**
     * Register this custom post type with WordPress.
     *
     * @return PostType The modified PostType instance.
     */
    public function register(): PostType
    {
        add_action($this->registration_tag, function () {
            register_post_type(
                post_type: $this->name,
                args: $this->props
            );
        });
        return $this;
    }

    /**
     * Unregister this custom post type.
     * Cannot be used to unregister built-in post types, use PostType->hide() instead.
     *
     * @return PostType The modified PostType instance.
     */
    public function unregister(): PostType
    {
        add_action($this->registration_tag, function () {
            unregister_post_type($this->name);
        });
        return $this;
    }

    /**
     * Check if this custom post type is registered.
     *
     * @return bool True if the post type is registered, false otherwise.
     */
    public function is_registered(): bool
    {
        return post_type_exists($this->name);
    }

    /**
     * Hide the post type from the UI and search results.
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
}
