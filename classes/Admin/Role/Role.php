<?php

namespace WP_Framework\Admin\Role;

/**
 * Class Role
 * Represents a WordPress user role with added functionalities.
 */
class Role extends \WP_Role
{
    /**
     * Get a role object by its name.
     *
     * @param string $role The name of the role.
     * @return self A Role object.
     */
    public static function get_role(string $role): self
    {
        $wp_role = (new \WP_Roles())->get_role($role);
        return new self(
            role: $wp_role->name,
            capabilities: $wp_role->capabilities
        );
    }

    /**
     * Register one or more capabilities to the role.
     *
     * @param string ...$capability One or more capabilities to register.
     * @return self The Role object with added capabilities.
     */
    public function register_capability(string ...$capabilty): self
    {
        # get stored to db each time...
        foreach ($capabilty as $capabilty) {
            $this->add_cap($capabilty);
        }
        return $this;
    }
}
