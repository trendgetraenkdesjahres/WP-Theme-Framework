# WP_Framework\Admin
The WP_Framework\Admin namespace provides tools and utilities for managing administrative tasks within WordPress websites. It offers functionalities to handle roles, tables, settings pages, and more.

## Roles
Roles in WordPress define the permissions and capabilities of users within the system. The WP_Framework\Admin namespace provides methods to manage roles efficiently.

### How to Add a Capability to a Role (Editor)
To add a capability to a role, such as Editor, follow these steps:

- Get the Editor role object using WP_Framework\Role\Role::get_role('editor').
- Use the add_cap() method to add the desired capability to the role object.
```php
$editor_role = Role::get_role('editor');
$editor_role->add_cap('edit_custom_post_type');
```

## Table & Editor
Use Tables to display Data. They are build to be used with `Custom Models`.

## Settings Page

### Add a Field to the General Options Page
To add a custom field to the General Options page, follow these steps:

- Create a settings screen instance for the WordPress 'Options General' page using `WP_Framework->get_admin_options_screen()`.
- Add the custom setting fields and setting sections with register_field(), register_section() or register_content(), which can do both.
- The option-name is the Sub Screen name (general, writing, ...), the key is the sanitized `Field->name` (check the Field with `Debug::var()` if unsure)

### Add a Custom Settings Page, Add Sub-Pages and Fields
To create a custom settings page, along with sub-pages and fields, follow these steps:

- Create a custom settings screen instance using `new PrimarySettingsScreen`.
- Optional: register Subpages with `PrimarySettingsScreen->register_sub_screen()`.
- Add the custom setting fields and setting sections with register_field(), register_section() or register_content(), which can do both, to the Primary or Sub Screens.
- Register the PrimarySettingsScreen instance with  `WP_Framework->register_admin_screen()`
- The option-name is the Sub Screen name (general, writing, ...), the key is the sanitized `Field->name` (check the Field with `Debug::var()` if unsure)
