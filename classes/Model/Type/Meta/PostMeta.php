<?php

namespace WP_Framework\Model\Type\Meta;

/**
 * Class PostMeta
 *
 * Represents a post meta field in WordPress.
 */
class PostMeta extends AbstractMeta implements MetaInterface
{
    /**
     * Registers the post meta field.
     * @param string $assign_to_object_type The type of post ('post', 'page', ...).
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
        add_action('add_meta_boxes', $this->edit($assign_to_object_type));

        # add save method to hook
        add_action('save_post', $this->save());
        return $this;
    }

    /**
     * Unregisters the post-type's meta field.
     * @param string $assigned_to_object_type The type of post ('post', 'page', ...).
     *
     * @return PostMeta The modified PostType instance.
     */
    public function unregister(string $assiged_to_object_type): PostMeta
    {
        # unregister meta
        unregister_meta_key('post', $this->meta_key, $assiged_to_object_type);

        # remove meta input from 'editor' screen
        remove_action('add_meta_boxes', $this->edit($assiged_to_object_type));

        # remove save method from hook
        remove_action('save_post', $this->save());
        return $this;
    }

    /**
     * Check if the post-type's meta field is registered.
     * @param string $assign_to_object_type The type of post ('post', 'page', ...).
     *
     * @return bool True if the post type is registered, false otherwise.
     */
    public function is_registered(string $assiged_to_object_type = ''): bool
    {
        return registered_meta_key_exists('post', $this->meta_key, $assiged_to_object_type);
    }


    /**
     * The Save Method which will be registred. Not really useful for you.
     *
     * @return callable The save function.
     */
    private function save(): callable
    {
        return function ($post_id) {
            if (!$this->is_saving_safe_and_secure($post_id, 'post')) {
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
     * @return callable The edit function.
     */
    private function edit($assign_to_object_type): callable
    {
        return function ($post) use ($assign_to_object_type) {
            $meta_box_callback = function ($post) {
                wp_nonce_field(
                    action: $this->input_field_action_name,
                    name: $this->input_field_nonce_name,
                    referer: true
                );
                echo $this->get_valid_input_field(
                    value: $this->get_current_value($post->ID, 'post')
                );
            };
            add_meta_box(
                id: $this->meta_key,
                title: $this->title,
                callback: $meta_box_callback,
                callback_args: [$post],
                screen: $assign_to_object_type,
                context: $this->input_field_position
            );
        };
    }
}
