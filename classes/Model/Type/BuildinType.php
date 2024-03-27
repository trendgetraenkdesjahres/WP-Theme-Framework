<?php

namespace WP_Framework\Model\Type;


/**
 * Handles a built-in model type (e.g., 'post-type' of posts or 'taxonomy' of terms) in WordPress.
 */
class BuildinType extends AbstractType
{
    public static function from_wp_type_object(\WP_Post_Type|\WP_Taxonomy $wp_type): BuildinType
    {
        if (property_exists($wp_type->labels, 'singular_name')) {
            $singular_name = $wp_type->labels->singular_name;
        } else {
            $singular_name = $wp_type->label;
        }
        $type =  new BuildinType(
            model_name: $wp_type->name,
            singular_name: $singular_name,
            plural_name: $wp_type->label,
            description: $wp_type->description
        );
        if (property_exists($wp_type, 'taxonomies')) {
            $type->set_taxonomies(...$wp_type->taxonomies);
        }
        return $type;
    }

    /**
     * Hide the post type from the UI and search results.
     *
     * @return BuildinType The modified BuildinType instance.
     */
    public function hide(): BuildinType
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
