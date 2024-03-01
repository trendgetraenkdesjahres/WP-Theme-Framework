<?php

namespace WP_ThemeFramework\MetaField;

use WP_Term;

class TermMeta extends MetaField implements MetaFieldInterface
{
    /**
     * Registers the term meta field.
     * @param string $assign_to_object_type The type of object ('category', 'post_tag', ...).
     *
     * @return TermMeta The current instance of TermMeta.
     */
    public function register(string $assign_to_object_type): TermMeta
    {
        # register meta
        $options = array_merge($this->options, [
            'object_subtype' => $assign_to_object_type,
            'type' => 'string',
            'description' => $this->description
        ]);
        register_meta('term', $this->meta_key, $options);

        # add meta input to 'edit' screen
        add_action(
            "{$assign_to_object_type}_edit_form_fields",
            $this->edit()
        );

        # add meta input to 'new' screen
        add_action(
            "{$assign_to_object_type}_add_form_fields",
            $this->edit()
        );

        # add save-method
        add_action('edit_term', $this->save(), 10, 1);
        add_action('create_term', $this->save(), 10, 1);
        return $this;
    }

    /**
     * The Save Method which will be registred. Not really useful for you.
     *
     * @return callable The save function.
     */
    private function save(): callable
    {
        return function ($term_id) {
            if (!self::is_safe_to_save($term_id, 'term')) {
                return $term_id;
            }
            if (!isset($_POST[$this->input_field_nonce_name]) || !wp_verify_nonce($_POST[$this->input_field_nonce_name], $this->input_field_action_name)) {
                return $term_id;
            }

            update_term_meta(
                term_id: $term_id,
                meta_key: $this->meta_key,
                meta_value: $_POST[$this->meta_key]
            );
            return $term_id;
        };
    }

    /**
     * The Edit Method which will be registred. Not really useful for you.
     *
     * @return callable The save function.
     */
    private function edit(): callable
    {
        return function (WP_Term|string $tag) {
            wp_nonce_field(
                action: $this->input_field_action_name,
                name: $this->input_field_nonce_name,
                referer: true
            );

            # when creating a new term, the parameter $tag is string of the taxonomy. a new term has no value yet.
            $input_field_value = null;
            if ($tag instanceof WP_Term) {
                $input_field_value = $this->get_current_value($tag->term_id, 'term');
            }
            echo "<tr class='form-field {$this->meta_key}'>
			<th scope='row'><label for='{$this->meta_key}'>{$this->name}</label></th>\n
			<td>" . $this->get_valid_input_field($input_field_value) . "</td>\n
		    </tr>";
        };
    }
}
