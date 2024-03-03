<?php

namespace WP_Framework\Model\Meta;

use WP_Comment;

/**
 * Class CommentMeta
 *
 * Represents a comment meta field in WordPress.
 */
class CommentMeta extends AbstractMeta implements MetaInterface
{

    public function register(): CommentMeta
    {
        # register meta
        $options = array_merge($this->options, [
            'type' => 'string',
            'description' => $this->description
        ]);
        register_meta('comment', $this->meta_key, $options);

        # add meta input to 'edit' screens
        add_action('comment_form_logged_in_after', $this->edit());
        add_action('comment_form_after_fields', $this->edit());
        add_action('add_meta_boxes_comment', $this->edit());

        # add save-method
        add_action('comment_post', $this->save());
        return $this;
    }

    /**
     * Unregisters the model's meta field.
     * @param string $assigned_to_object_type The taxonomy ('category', 'post_tag', ...).
     *
     * @return CommentMeta The modified PostType instance.
     */
    public function unregister(): CommentMeta
    {
        # unregister meta
        unregister_meta_key('comment', $this->meta_key);

        # remove meta input from 'editor' screen
        remove_action('comment_form_logged_in_after', $this->edit());
        remove_action('comment_form_after_fields', $this->edit());
        remove_action('add_meta_boxes_comment', $this->edit());

        # remove save method from hooks
        remove_action('comment_post', $this->save());
        return $this;
    }

    /**
     * Check if the model's meta field is registered.
     *
     * @return bool True if the post type is registered, false otherwise.
     */
    public function is_registered(): bool
    {
        return registered_meta_key_exists('comment', $this->meta_key);
    }

    /**
     * The Save Method which will be registred. Not really useful for you.
     *
     * @return callable The save function.
     */
    private function save(): callable
    {
        return function ($comment_id) {
            if (!$this->is_saving_safe_and_secure($comment_id, 'comment')) {
                return $comment_id;
            }
            update_comment_meta(
                comment_id: $comment_id,
                meta_key: $this->meta_key,
                meta_value: $this->cast_to_data_type($_POST[$this->meta_key])
            );
            return $comment_id;
        };
    }

    /**
     * The Public Form Method which will be registred. Not really useful for you.
     *
     * @return callable The public_form function.
     */
    private function edit(): callable
    {
        return function ($comment) {
            wp_nonce_field(
                action: $this->input_field_action_name,
                name: $this->input_field_nonce_name,
                referer: true
            );

            # when creating a new term, the parameter $tag is string of the taxonomy. a new term has no value yet.
            $input_field_value = null;
            if ($comment instanceof WP_Comment) {
                $input_field_value = $this->get_current_value($comment->comment_ID, 'comment');
            }
            echo "<table class='form-table'><tr>\n
            <th><label for='{$this->meta_key}'>{$this->name}</label></th>\n
			<td>" . $this->get_valid_input_field($input_field_value) . "</td>\n
		    </tr></table>";
        };
    }
}
