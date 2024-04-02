<?php

namespace WP_Framework\Model\Meta\Buildin;


/**
 * Class PostMeta
 *
 * Represents a post meta field in WordPress.
 */
class PostMeta extends AbstractBuildinMeta
{
    public array $edit_hooks = [
        'add_meta_boxes'
    ];

    public array $save_hooks = [
        'save_post'
    ];

    public function get_edit_callback(?string $model_type = null): callable
    {
        return function ($post) use ($model_type) {
            $meta_box_callback = function ($post) {
                echo $this->get_nonce_field();
                echo $this->get_form_control(
                    value: $this->get_current_value($post->ID, 'post')
                );
            };
            add_meta_box(
                id: $this->key,
                title: $this->name,
                callback: $meta_box_callback,
                callback_args: [$post],
                screen: $model_type,
                context: $this->input_field_position
            );
        };
    }
}
