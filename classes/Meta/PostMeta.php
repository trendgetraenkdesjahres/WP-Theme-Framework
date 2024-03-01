<?php

namespace WP_ThemeFramework\Meta;

/**
 * Class PostMeta
 *
 * Represents a post meta field in WordPress.
 */
class PostMeta extends Meta implements MetaInterface
{
    /**
     * Registers the post meta field.
     * @param string $assign_to_object_type The type of object ('post', 'page', ...).
     *
     * @return PostMeta The current instance of PostMeta.
     */
    public function register(string $assign_to_object_type): PostMeta
    {
        # register meta
        $options = array_merge($this->options, [
            'object_subtype' => $assign_to_object_type,
            'type' => $this->get_data_type(),
            'description' => $this->description
        ]);
        register_meta('post', $this->meta_key, $options);

        # add meta input to 'editor' screen
        add_action('add_meta_boxes', function () use ($assign_to_object_type) {
            add_meta_box(
                id: $this->meta_key,
                title: $this->title,
                callback: $this->edit(),
                screen: $assign_to_object_type,
                context: $this->input_field_position
            );
        });
        add_action('save_post', $this->save(), 10, 1);
        return $this;
    }

    /**
     * The Save Method which will be registred. Not really useful for you.
     *
     * @return callable The save function.
     */
    private function save(): callable
    {
        return function ($post_id) {
            if (!self::is_safe_to_save($post_id, 'post')) {
                return $post_id;
            }

            if (!isset($_POST[$this->input_field_nonce_name]) || !wp_verify_nonce($_POST[$this->input_field_nonce_name], $this->input_field_action_name)) {
                return $post_id;
            }

            update_post_meta(
                post_id: $post_id,
                meta_key: $this->meta_key,
                meta_value: $this->cast_to_data_type($_POST[$this->meta_key])
            );
            return $post_id;
        };
    }

    /**
     * The Edit Method which will be registred. Not really useful for you.
     *
     * @return callable The save function.
     */
    private function edit(): callable
    {
        return function ($post) {
            wp_nonce_field(
                action: $this->input_field_action_name,
                name: $this->input_field_nonce_name,
                referer: true
            );

            echo $this->get_valid_input_field(
                value: $this->get_current_value($post->ID, 'post')
            );
        };
    }
}
