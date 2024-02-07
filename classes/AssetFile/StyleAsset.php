<?php

namespace WP_ThemeFramework\AssetFile;

class StyleAsset extends AssetFile implements AssetFileInterface
{
    protected string $default_action_hook = 'enqueue_styles';
    protected string $media = 'all';

    public function register(): StyleAsset
    {
        add_action($this->action_hook, function () {
            if (false === wp_register_style($this->handle, $this->url, $this->dependencies, $this->version, $this->media)) {
                throw new \Error('Could not register ' . $this->handle);
            }
        });
        return $this;
    }

    public function set_tag_attributes(array $key_value_pairs): StyleAsset
    {
        return $this;
    }

    public function enqueue(): StyleAsset
    {
        add_action($this->action_hook, function () {
            if (false === wp_enqueue_style($this->handle, $this->url, $this->dependencies, $this->version, $this->media)) {
                throw new \Error("Could not enqueue '$this->handle' with function add_action('$this->action_hook', function () {\nwp_enqueue_style('$this->handle', '$this->url', ['" . implode("', '", $this->dependencies) . "'], '$this->version', " . ($in_footer ? 'true' : 'false') . ");\n});.");
            }
        });
        return $this;
    }
}