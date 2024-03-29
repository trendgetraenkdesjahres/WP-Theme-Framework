<?php

namespace WP_Framework\Model\Property;

use WP_Framework\Database\Database;
use WP_Framework\Element\Input\FormControlElement;
use WP_Framework\Model\BuildinModel;
use WP_Framework\Model\CustomModel;

class ForeignInstance extends Property
{
    public string $reference_table;

    public string $reference_id_column;

    public function __construct(BuildinModel|CustomModel $referenced_model)
    {
        $this->reference_table = $referenced_model->get_table()->name;
        $this->reference_id_column = $referenced_model->get_table()->id_column_name;

        # get fancy names
        if ($referenced_model instanceof CustomModel) {
            $singular_name = $referenced_model->singular_name;
            $plural_name = $referenced_model->plural_name;
        } else {
            $singular_name = $referenced_model->name;
            $plural_name = $singular_name . 's';
        }

        parent::__construct(
            singular_name: $singular_name,
            plural_name: $plural_name,
            sql_type: 'bigint(20)',
            is_indexable: true
        );
    }

    /**
     * Get the form control element for the property.
     *
     * @param mixed $value The value to set on the form control.
     *
     * @return FormControlElement The form control element.
     */
    public function get_form_control($value): FormControlElement
    {
        $form_control = $this->form_control;
        if (!$form_control && $title_column = $this->get_buildin_property_title_column()) {
            $options = [];
            $reference_table = Database::get_table($this->reference_table);
            $result = $reference_table
                ->select(
                    $reference_table->id_column_name,
                    $title_column
                )
                ->execute();
            $options = [];
            foreach ($result as $foreign_insance) {
                $options[$foreign_insance[$reference_table->id_column_name]] = $foreign_insance[$title_column];
            }
            $form_control = new FormControlElement('input', ['id' => $this->key], $this->singular_name, $options);
        }

        # TODO what if i dont know the property_title_column???
        return $form_control
            ->set_value($value)
            ->set_id($this->key);
    }

    private function get_buildin_property_title_column(): ?string
    {
        $title_columns = [
            'wp_comments' => 'comment_ID',
            'wp_links' => 'link_name',
            'wp_options' => 'option_name',
            'wp_posts' => 'post_name',
            'wp_terms' => 'name',
            'wp_term_taxonomy' => 'taxonomy',
            'wp_users' => 'display_name'
        ];
        return isset($title_columns[$this->reference_table]) ? $title_columns[$this->reference_table] : null;
    }
}
