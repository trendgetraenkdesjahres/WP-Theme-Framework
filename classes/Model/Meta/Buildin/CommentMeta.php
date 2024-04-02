<?php

namespace WP_Framework\Model\Meta\Buildin;


/**
 * Class CommentMeta
 *
 * Represents a comment meta field in WordPress.
 */
class CommentMeta extends AbstractBuildinMeta
{
    public array $edit_hooks = [
        'comment_form_logged_in_after',
        'comment_form_after_fields',
        'add_meta_boxes_comment'
    ];
    public array $save_hooks = [
        'comment_post'
    ];

    public function get_edit_callback(?string $model_type = null): callable
    {
        return function (\WP_Comment|string $comment) {
            # when creating a new comment, the parameter $comment is string of the taxonomy. a new $comment has no value yet.
            $input_field_value = null;
            if ($comment instanceof \WP_Comment) {
                $input_field_value = $this->get_current_value($comment->comment_ID, 'comment');
            }
            echo $this->get_nonce_field();
            echo "<table class='form-table'><tr>\n
            <th><label for='{$this->key}'>{$this->name}</label></th>\n
			<td>" . $this->get_form_control($input_field_value) . "</td>\n
		    </tr></table>";
        };
    }
}
