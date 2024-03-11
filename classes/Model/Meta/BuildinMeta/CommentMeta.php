<?php

namespace WP_Framework\Model\Meta\Buildin;

use WP_Comment;
use WP_Framework\Model\Meta\AbstractMeta;

/**
 * Class CommentMeta
 *
 * Represents a comment meta field in WordPress.
 */
class CommentMeta extends AbstractMeta
{
    public array $edit_hooks = [
        'comment_form_logged_in_after',
        'comment_form_after_fields',
        'add_meta_boxes_comment'
    ];
    public array $save_hooks = [
        'comment_post'
    ];

    public function get_save_callback(): callable
    {
        return function ($comment_id) {
            if (!$this->is_saving_safe_and_secure($comment_id, 'comment')) {
                return $comment_id;
            }
            update_comment_meta(
                comment_id: $comment_id,
                meta_key: $this->key,
                meta_value: $this->cast_to_data_type($_POST[$this->key])
            );
            return $comment_id;
        };
    }

    public function get_edit_callback(): callable
    {
        return function ($comment) {
            wp_nonce_field(
                action: $this->input_field_action_name,
                name: $this->input_field_nonce_name,
                referer: true
            );

            # when creating a new comment, the parameter $comment is string of the taxonomy. a new $comment has no value yet.
            $input_field_value = null;
            if ($comment instanceof WP_Comment) {
                $input_field_value = $this->get_current_value($comment->comment_ID, 'comment');
            }
            echo "<table class='form-table'><tr>\n
            <th><label for='{$this->key}'>{$this->name}</label></th>\n
			<td>" . $this->get_valid_input_field($input_field_value) . "</td>\n
		    </tr></table>";
        };
    }
}
