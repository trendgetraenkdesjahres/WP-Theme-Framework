<?php

namespace WP_ThemeFramework\CustomTaxonomy;

class CustomTaxonomy
{
    public CustomTaxonomyFile $taxonomy_file;

    public function __construct(string $path)
    {
        $this->taxonomy_file = new CustomTaxonomyFile($path);
    }

    public function register()
    {
        return register_taxonomy(
            taxonomy: $this->taxonomy_file->taxonomy_name,
            object_type: $this->taxonomy_file->taxonomy_object,
            args: $this->taxonomy_file->taxonomy_args
        );
    }
}
