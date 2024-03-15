<?php

namespace WP_Framework\Admin\Role;

class Role extends \WP_Role{

    public static function get_role(string $role): self {
        $wp_role = (new \WP_Roles())->get_role($role);
        return new self(
            role: $wp_role->name,
            capabilities: $wp_role->capabilities
        );
    }

    public function register_capability(string ...$capabilty): self
    {
        # get stored to db each time...
        foreach($capabilty as $capabilty) {
            $this->add_cap($capabilty);
        }
        return $this;
    }
}