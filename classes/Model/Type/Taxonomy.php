<?php

namespace WP_Framework\Model\Type;


/**
 * Handles a (custom) taxonomy (aka a type of term) in WordPress.
 */
class Taxonomy extends AbstractType implements TypeInterface
{
    protected string $model_name = 'term';

    /**
     * Register this custom taxonomy with WordPress.
     *
     * @return Taxonomy The modified PostType instance.
     */
    public function register(): Taxonomy
    {
        add_action($this->registration_tag, function () {
            register_taxonomy(
                taxonomy: $this->name,
                object_type: $this->props['object_type'],
                args: $this->props
            );
        });
        return $this;
    }

    /**
     * Unregister this taxonomy.
     * Cannot be used to unregister built-in taxonomies, use Taxonomy->hide() instead.
     * @return Taxonomy The modified PostType instance.
     */
    public function unregister(): Taxonomy
    {
        add_action($this->registration_tag, function () {
            unregister_taxonomy($this->name);
        });
        return $this;
    }

    /**
     * Check if this custom taxonomy is registered.
     *
     * @return bool True if the post type is registered, false otherwise.
     */
    public function is_registered(): bool
    {
        return taxonomy_exists($this->name);
    }

    /**
     * Hide the taxonomy from the UI and search results.
     *
     * @return Taxonomy The modified Taxonomy instance.
     */
    public function hide(): Taxonomy
    {
        add_filter("register_taxonomy_args", function ($args, $taxonomy) {
            if ($this->name !== $taxonomy) {
                return $args;
            }
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
        }, 10, 2);
        return $this;
    }
}
