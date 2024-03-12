### WP_Framework Model Package
WordPress often requires handling custom data structures beyond posts and pages. The Model package aims to simplify the process of defining and managing these custom structures, offering a clean and object-oriented interface.

## Features
- Custom Post Types, Custom Taxonomies: Create and manage custom post types using a JSON-based configuration.
- Meta Fields: Integrate and manage meta fields associated with posts, users, or other custom structures.
- New Models: Define custom data models for various aspects of your plugin instead of overstretching a post object with a new post-type.

## Examples of Usage

### Adding a Custom Post Type
```
$post_model = new BuildinModel('post');
// Define a new post type for 'Books'
$book = PostType::create_type_with_json('book', [
    'label' => 'Books',
    'public' => true,
    // Add more configuration as needed
]);
$post_model->register_type($book);
```

### Adding a Meta Field to Users
```
$user_model = new BuildinModel('user');
// Define a text meta field for 'users'
$user_bio = UserMeta::create_text('user_bio', 'User Biography');
$user_model->register_meta($book);
```

## Agenda
 - [ ] add custom meta
 - [ ] add custom types
 - [ ] add more factory methods
 - [ ] add foreign key to custom model properties
