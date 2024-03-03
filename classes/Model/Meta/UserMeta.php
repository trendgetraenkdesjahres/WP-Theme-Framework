<?php

namespace WP_Framework\Model\Meta;

use WP_User;

/**
 * Class UserMeta
 *
 * Represents a user meta field in WordPress.
 */
class UserMeta extends AbstractMeta implements MetaInterface
{

    public function register(): UserMeta
    {
        # register meta
        $options = array_merge($this->options, [
            'type' => 'string',
            'description' => $this->description
        ]);
        register_meta('user', $this->meta_key, $options);

        # add meta input to 'edit' screens
        add_action('show_user_profile', $this->edit());
        add_action('edit_user_profile', $this->edit());

        # add save-method
        add_action('personal_options_update', $this->save());
        add_action('edit_user_profile_update', $this->save());
        return $this;
    }

    /**
     * Unregisters the model's meta field.
     * @param string $assigned_to_object_type The taxonomy ('category', 'post_tag', ...).
     *
     * @return TermMeta The modified PostType instance.
     */
    public function unregister(): UserMeta
    {
        # unregister meta
        unregister_meta_key('user', $this->meta_key);

        # remove meta input from 'editor' screen
        remove_action('show_user_profile', $this->edit());
        remove_action('edit_user_profile', $this->edit());

        # remove save method from hooks
        remove_action('edit_term', $this->save());
        remove_action('create_term', $this->save());
        return $this;
    }

    /**
     * Check if the model's meta field is registered.
     *
     * @return bool True if the post type is registered, false otherwise.
     */
    public function is_registered(): bool
    {
        return registered_meta_key_exists('user', $this->meta_key);
    }

    /**
     * The Save Method which will be registred. Not really useful for you.
     *
     * @return callable The save function.
     */
    private function save(): callable
    {
        return function ($user_id) {
            if (!$this->is_saving_safe_and_secure($user_id, 'user')) {
                return $user_id;
            }
            update_user_meta(
                user_id: $user_id,
                meta_key: $this->meta_key,
                meta_value: $this->cast_to_data_type($_POST[$this->meta_key])
            );
            return $user_id;
        };
    }

    /**
     * The Edit Method which will be registred. Not really useful for you.
     *
     * @return callable The save function.
     */
    private function edit(): callable
    {
        return function ($user) {
            wp_nonce_field(
                action: $this->input_field_action_name,
                name: $this->input_field_nonce_name,
                referer: true
            );

            # when creating a new term, the parameter $tag is string of the taxonomy. a new term has no value yet.
            $input_field_value = null;
            if ($user instanceof WP_User) {
                $input_field_value = $this->get_current_value($user->ID, 'user');
            }
            echo "<table class='form-table'><tr>\n
            <th><label for='{$this->meta_key}'>{$this->name}</label></th>\n
			<td>" . $this->get_valid_input_field($input_field_value) . "</td>\n
		    </tr></table>";
        };
    }
}
