<?php

namespace WP_Framework\Model\Meta\Buildin;

/**
 * Class PostMeta
 *
 * Represents a term meta field in WordPress.
 */
class TermMeta extends AbstractBuildinMeta
{

    public array $save_hooks = [
        'edit_term',
        'create_term'
    ];

    public function get_edit_hooks(?string $model_type = null): array
    {
        return ["{$model_type}_edit_form_fields"];
    }

    public function get_edit_callback(?string $model_type = null): callable
    {
        return function (\WP_Term|string $tag) {
            wp_nonce_field(
                action: $this->input_field_action_name,
                name: $this->input_field_nonce_name,
                referer: true
            );

            # when creating a new term, the parameter $tag is string of the taxonomy. a new term has no value yet.
            $input_field_value = null;
            if ($tag instanceof \WP_Term) {
                $input_field_value = $this->get_current_value($tag->term_id, 'term');
            }
            echo "<tr class='form-field {$this->key}'>\n
			<th scope='row'><label for='{$this->key}'>{$this->name}</label></th>\n
			<td>" . $this->get_form_control($input_field_value) . "</td>\n
		    </tr>";
        };
    }
}
