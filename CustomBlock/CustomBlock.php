<?php

namespace WP_ThemeFramework\CustomBlock;

class CustomBlock
{
    public string $theme_name;
    public string $name;
    public array $args;
    protected CustomBlockFile $custom_block_file;

    public function __construct(string $path)
    {
        $this->theme_name = get_stylesheet();
        $this->custom_block_file = new CustomBlockFile($path);
        $this->name = $this->theme_name . "/" . $this->custom_block_file->block_name;
        $this->args = array_merge(
            $this->get_block_default_args(),
            $this->custom_block_file->block_args,
            $this->get_block_forced_args()
        );
    }

    public function register()
    {
        return register_block_type(
            $this->name,
            $this->args
        );
    }

    protected function get_block_default_args(): array
    {
        $args = [];
        $args['$schema'] = "https://schemas.wp.org/trunk/block.json";
        $args['apiVersion'] = 3;
        $args['selectors'] = [
            'root' => ".{$this->name}"
        ];
        $args['styles'] = [[
            'name' => 'default',
            'label' => 'Default',
            'isDefault' => true
        ]];
        return $args;
    }

    protected function get_block_forced_args(): array
    {
        $args = [];
        $args['name'] = "{$this->name}";
        $args['textdomain'] = $this->theme_name;

        if (
            $defined_asset = $this->register_js_asset("script.js")
        ) {
            $args['script'] = $defined_asset;
        }

        if (
            $defined_asset = $this->register_js_asset("editor.js", 'admin')
        ) {
            $args['editorScript'] = $defined_asset;
        }

        if (
            $defined_asset = $this->register_js_asset("view.js")
        ) {
            $args['viewScript'] = $defined_asset;
        }

        if (
            $defined_asset = $this->register_css_asset("editor.css", 'admin')
        ) {
            $args['editorStyle'] = $defined_asset;
        }

        if (
            $defined_asset = $this->register_css_asset("view.css")
        ) {
            $args['style'] = $defined_asset;
        }

        if (!file_exists(
            $render_php = $this->custom_block_file->path . "/render.php"
        )) {
            throw new \Error("$render_php does not exist.");
        }
        $args['render'] = "file:./" . basename($render_php);

        return $args;
    }


    /**
     * Method register_js_asset
     *
     * @param string $filename [explicite description]
     * @param string $hook wp, admin or login
     *
     * @return array|false
     */
    private function register_js_asset(string $filename, string $enqueue_hook = 'wp'): array|false
    {
        $script_js_handle = "{$this->name}--script";
        if (file_exists($script_js = $this->custom_block_file->path . "/$filename")) {
            add_action("{$enqueue_hook}_enqueue_scripts", function () use ($script_js_handle, $script_js) {
                wp_register_script($script_js_handle, '/' . str_replace(ABSPATH, '', $script_js));
            });
            add_filter('wp_script_attributes', function ($attributes) use ($script_js_handle) {
                if (isset($attributes['id']) && $attributes['id'] === "{$script_js_handle}-js") {
                    $attributes['type'] = 'module';
                }
                return $attributes;
            }, 10, 1);
            return ["file:./" . basename($script_js), $script_js_handle];
        }
        return false;
    }

    /**
     * Method register_css_asset
     *
     * @param string $filename [explicite description]
     * @param string $hook wp, admin or login
     *
     * @return array|false
     */
    private function register_css_asset(string $filename, string $enqueue_hook = 'wp'): array|false
    {
        $script_css_handle = "{$this->name}--style";
        if (file_exists($script_css = $this->custom_block_file->path . "/$filename")) {
            add_action("{$enqueue_hook}_enqueue_scripts", function () use ($script_css_handle, $script_css) {
                wp_register_style($script_css_handle, '/' . str_replace(ABSPATH, '', $script_css));
            });
            return ["file:./" . basename($script_css), $script_css_handle];
        }
        return false;
    }
}
