<?php

namespace WP_ThemeFramework\CustomBlock;

use WP_ThemeFramework\AssetFile\ScriptAsset;
use WP_ThemeFramework\AssetFile\StyleAsset;

class CustomBlock
{
    public string $theme_name;
    public string $name;
    public string $short_name;
    public array $args;
    public array $args_json;
    protected CustomBlockFile $custom_block_file;
    protected string $render_php;

    public function __construct(string $path)
    {
        $this->theme_name = get_stylesheet();
        $this->custom_block_file = new CustomBlockFile($path);
        $this->name = $this->theme_name . "/" . $this->custom_block_file->block_name;
        $this->short_name = $this->custom_block_file->block_name;
        $this->args = array_merge(
            $this->get_block_default_args(),
            $this->custom_block_file->block_args,
            $this->get_block_forced_args()
        );
    }

    public function render($block_attributes, $content)
    {
        ob_start();
        require $this->render_php;
        return ob_get_clean();
    }
    public function register()
    {
        add_filter('customBlocksData', function ($data) {
            $data[$this->short_name] = $this->get_js_args();
            return $data;
        });
        register_block_type(
            $this->name,
            $this->args
        );
    }

    public function get_js_args()
    {
        $filtered_args = $this->args;
        /* 'render_callback' is a this CustomBlock (-> Recursion), so it needs to drop in order for json-encoding */
        unset(
            $filtered_args['render_callback']
        );

        /* convert to camelCase */
        foreach ($filtered_args as $argument => $value) {
            unset($filtered_args[$argument]);
            $camel_case_argument = '';
            foreach (explode('_', $argument) as $index => $word) {
                if ($index > 0) $word = $word = ucfirst($word);
                $camel_case_argument .= $word;
            }
            $filtered_args[$camel_case_argument] = $value;
        }
        return $filtered_args;
    }
    protected function get_block_default_args(): array
    {
        $args = [];
        $args['$schema'] = "https://schemas.wp.org/trunk/block.json";
        $args['api_version'] = 3;
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
            $args['editor_script'] = $defined_asset;
        }

        if (
            $defined_asset = $this->register_js_asset("view.js")
        ) {
            $args['view_script'] = $defined_asset;
        }

        if (
            $defined_asset = $this->register_css_asset("editor.css", 'admin')
        ) {
            $args['editor_style'] = $defined_asset;
        }

        if (
            $defined_asset = $this->register_css_asset("view.css")
        ) {
            $args['style'] = $defined_asset;
        }

        if (!file_exists(
            $this->render_php = $this->custom_block_file->path . "/render.php"
        )) {
            throw new \Error("{$this->render_php} does not exist.");
        }
        $args['render'] = "file:./" . basename($this->render_php);
        $args['render_callback'] = [$this, 'render'];

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
    private function register_js_asset(string $filename, string $use_in = 'wp'): array
    {
        $script_asset = new ScriptAsset(
            path: $this->custom_block_file->path . "/$filename",
            action_hook: 'enqueue_block_assets',
            use_in: $use_in
        );
        $script_asset
            ->register()
            ->set_tag_attributes(['type' => 'module']);
        return ["file:./" . basename($filename), $script_asset->handle];
    }

    /**
     * Method register_css_asset
     *
     * @param string $filename [explicite description]
     * @param string $hook wp, admin or login
     *
     * @return array|false
     */
    private function register_css_asset(string $filename, string $use_in = 'wp'): array|false
    {
        $script_asset = new StyleAsset(
            path: $this->custom_block_file->path . "/$filename",
            action_hook: 'enqueue_block_assets',
            use_in: $use_in
        );
        $script_asset
            ->register();
        return ["file:./" . basename($filename), $script_asset->handle];
    }
}