<?php

namespace WP_Framework\Model\Type;

/**
 * Handles the type of a custom DataObject in WordPress.
 */
class DataType extends AbstractType
{
    protected string $model_name = 'data';

    /**
     * Register this type of a custom DataObject with WordPress.
     *
     * @return DataType The modified DataType instance.
     */
    public function register(): DataType
    {
        global $wp_objects_types;

        if (!is_array($wp_dataobject_types)) {
            $wp_dataobject_types = array();
        }

        if (!$this->name || strlen($this->name) > 20) {
            throw new \Error('Post type names must be between 1 and 20 characters in length.');
        }

        $this->add_rewrite_rules();
        $wp_dataobject_types[$this->name] = $this;
        $this->add_hooks();

        return $this;
    }
}
