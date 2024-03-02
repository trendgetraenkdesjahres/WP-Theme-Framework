<?php

namespace WP_Framework\AssetFile;

class ScriptAsset extends AssetFile implements AssetFileInterface
{
    protected string $default_action_hook = 'enqueue_scripts';
    protected bool $in_footer = false;
    protected ?string $strategy = null;

    public function register(): ScriptAsset
    {
        add_action($this->action_hook, function () {
            if (false === wp_register_script($this->handle, $this->url, $this->dependencies, $this->version, $this->get_composed_args())) {
                throw new \Error('Could not register ' . $this->handle);
            }
        });
        return $this;
    }

    /**
     * Sets tag attributes for the asset.
     *
     * @param array $key_value_pairs Array of key-value pairs for attributes.
     * @return ScriptAsset
     */
    public function set_tag_attributes(array $key_value_pairs): ScriptAsset
    {
        add_filter('wp_script_attributes', function ($attributes) use ($key_value_pairs) {
            if (isset($attributes['id']) && str_ends_with($attributes['id'], "js") && str_starts_with($attributes['id'], "$this->handle")) {
                $attributes = array_merge($attributes, $key_value_pairs);
            }
            return $attributes;
        }, 10, 1);
        return $this;
    }

    /**
     * Prepares a hook in order to pass data to the asset.
     * Create and supplement the data later by using the
     *
     * add_filter('hookName', 'my_data_modifier');
     * my_data_modifier($data) {
     * return $data + 1;
     * }
     *
     * @param string $hook_name Name of the hook and the variable name for the script (use camelCase!)
     * @param string $type the type of data. need to be an php inbuild type
     * @return ScriptAsset
     */
    public function add_data_hook(string $hook_name, string $type = 'array'): ScriptAsset
    {
        $data = null;
        if (!settype($data, $type)) {
            throw new \Error("'$data' is not a type.");
        }
        add_action($this->action_hook, function () use ($hook_name, $data) {
            if (!wp_localize_script($this->handle, $hook_name, apply_filters($hook_name, $data))) {
                throw new \Error('Could not run wp_localize_script for ' . $this->handle);
            }
        }, 999);
        return $this;
    }

    public function enqueue(): ScriptAsset
    {
        add_action($this->action_hook, function () {
            if (false === wp_enqueue_script($this->handle, $this->url, $this->dependencies, $this->version, $this->get_composed_args())) {
                throw new \Error("Could not enqueue '$this->handle' with function add_action('$this->action_hook', function () {\n{$this->use_in}_enqueue_script('$this->handle', '$this->url', ['" . implode("', '", $this->dependencies) . "'], '$this->version', " . ($in_footer ? 'true' : 'false') . ");\n});.");
            }
        });
        return $this;
    }

    public function set_in_footer(bool $in_footer): ScriptAsset
    {
        $this->$in_footer = $in_footer;
        return $this;
    }
    public function set_strategy(string $strategy): ScriptAsset
    {
        $valid_strategies = [
            'defer',
            'async'
        ];
        if (!in_array($strategy, $valid_strategies)) {
            throw new \Error("'$strategy' is not a valid strategy.");
        }
        $this->strategy = $strategy;
        return $this;
    }
    private function get_composed_args(): array
    {
        $args = ["in_footer" => $this->in_footer];
        if ($this->strategy) {
            $args['strategy'] = $this->strategy;
        }
        return $args;
    }
}
