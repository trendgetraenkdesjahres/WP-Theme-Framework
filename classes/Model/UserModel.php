<?php

namespace WP_Framework\Model;

use WP_User;

/**
 * Handles the UserModel in WordPress.
 */
class UserModel extends AbstractModel implements ModelInterface
{
    public string $name = 'user';
    protected ?string $type_class = null;
    protected ?string $types_json_folder = null;

    public function get_buildin_object(int $object_id): object
    {
        return new WP_User($object_id);
    }
}
