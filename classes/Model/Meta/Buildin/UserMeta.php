<?php

namespace WP_Framework\Model\Meta\Buildin;


/**
 * Class UserMeta
 *
 * Represents a user meta field in WordPress.
 */
class UserMeta extends AbstractBuildinMeta
{
    public array $edit_hooks = [
        'show_user_profile',
        'edit_user_profile'
    ];
    public array $save_hooks = [
        'personal_options_update',
        'edit_user_profile_update',
    ];

    public function get_edit_callback(?string $model_type = null): callable
    {
        return function (\WP_User|string $user) {
            wp_nonce_field(
                action: $this->input_field_action_name,
                name: $this->input_field_nonce_name,
                referer: true
            );

            # when creating a new term, the parameter $tag is string of the taxonomy. a new term has no value yet.
            $input_field_value = null;
            if ($user instanceof \WP_User) {
                $input_field_value = $this->get_current_value($user->ID, 'user');
            }
            echo "<table class='form-table'><tr>\n
            <th><label for='{$this->key}'>{$this->name}</label></th>\n
			<td>" . $this->get_form_control($input_field_value) . "</td>\n
		    </tr></table>";
        };
    }
}
