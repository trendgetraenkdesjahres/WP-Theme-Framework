<?php

namespace WP_Framework\Model;

/**
 * Handles the BuildinModels in WordPress.
 */
class BuildinModel extends AbstractModel
{
    public function __construct(
        public string $name,
        public ?string $type_class = null,
        public ?string $types_json_folder = null,
        public bool $has_meta = false
    ) {
    }

    public function get_buildin_object(int $object_id): object
    {
        switch ($this->name) {
            case 'post':
                return \WP_Post::get_instance($object_id);
            case 'comment':
                return \WP_Comment::get_instance($object_id);
            case 'term':
                return \WP_Term::get_instance($object_id);
            case 'user':
                return new \WP_User($object_id);
            default:
                throw new \Error("'$this->name' has no 'get_buildin_object'-method.");
        }
    }
}
