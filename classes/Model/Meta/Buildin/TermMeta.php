<?php

namespace WP_Framework\Model\Meta\Buildin;

/**
 * Class TermMeta
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
        return [
            "{$model_type}_add_form_fields",
            "{$model_type}_edit_form_fields"
        ];
    }

    public function get_edit_callback(?string $model_type = null): callable
    {
        return function (\WP_Term|string $tag) {

            # when creating a new term, the parameter $tag is string of the taxonomy. a new term has no value yet.
            $input_field_value = null;
            if ($tag instanceof \WP_Term) {
                $input_field_value = $this->get_current_value($tag->term_id, 'term');
            }

            echo $this->get_nonce_field();
            echo "<tr class='form-field {$this->name}'>\n
			<th scope='row'><label for='{$this->name}'>{$this->title}</label></th>\n
			<td>" . $this->get_form_control($input_field_value) . "</td>\n
		    </tr>";
        };
    }
}
