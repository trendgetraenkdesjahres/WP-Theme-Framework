<?php

namespace WP_ThemeFramework\CustomPostType;

class CustomPostType
{
    public CustomPostTypeFile $posttype_file;

    public function __construct(string $path)
    {
        $this->posttype_file = new CustomPostTypeFile($path);
    }

    public function register()
    {
        return register_post_type(
            post_type: $this->posttype_file->posttype_name,
            args: $this->posttype_file->posttype_args
        );
    }
}
