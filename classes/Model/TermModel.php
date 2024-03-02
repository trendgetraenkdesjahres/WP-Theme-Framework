<?php

namespace WP_Framework\Model;

use WP_Term;

/**
 * Handles the TermModel in WordPress.
 */
class TermModel extends AbstractModel implements ObjectInterface
{
    protected string $type_class = 'Taxonomy';
    protected string $types_json_folder = 'taxonomies';

    public function get_buildin_object(int $object_id): object
    {
        return WP_Term::get_instance($object_id);
    }
}
