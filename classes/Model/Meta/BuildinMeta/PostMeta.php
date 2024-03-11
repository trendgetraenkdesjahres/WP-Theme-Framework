<?php

namespace WP_Framework\Model\Meta\Buildin;

use WP_Framework\Model\Meta\AbstractMeta;

/**
 * Class PostMeta
 *
 * Represents a post meta field in WordPress.
 */
class PostMeta extends AbstractMeta
{
    public array $edit_hooks = [
        'add_meta_boxes'
    ];
    public array $save_hooks = [
        'save_post'
    ];

    public function get_save_callback(): callable
    {
        return function ($post_id) {
            if (!$this->is_saving_safe_and_secure($post_id, 'post')) {
                return $post_id;
            }
            update_post_meta(
                post_id: $post_id,
                meta_key: $this->key,
                meta_value: $this->cast_to_data_type($_POST[$this->key])
            );
            return $post_id;
        };
    }

    public function get_edit_callback(): callable
    {
        return function ($post) {
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
                id: $this->key,
                title: $this->title,
                callback: $meta_box_callback,
                callback_args: [$post],
                screen: 'post',
                context: $this->input_field_position
            );
        };
    }
}
