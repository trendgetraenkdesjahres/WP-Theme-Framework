<?php

namespace WP_Framework\Debug;

use WP_Framework\AssetFile\StyleAsset;

class Debug
{

    private static $instance;

    private string $element_class = 'fw_debug';
    private string $element_tag = 'section';

    private array $dumps = [];

    protected string $action_hook = '';

    private array $style = [
        'border' => '1px solid #c3c4c7',
        'border-top' => 'none',
        'background' => 'white',
        'border-radius' => '0 0 4px 4px',
        'padding-inline' => '1rem',
        'height' => 'fit-content',
        'max-height' => 'calc(100vh - 46px)',
        'overflow' => 'auto',
        'resize' => 'vertical'
    ];

    protected function add_var(mixed ...$var)
    {
        $vars = [];
        foreach ($var as $var) {
            array_push($vars, [
                'type' => ($type = gettype($var)) == 'object' ? get_class($var) : $type,
                'value' => print_r($var, true)
            ]);
        }
        $caller = debug_backtrace(limit: 2)[1];
        $caller_location = str_replace(
            search: getcwd() . "/",
            replace: '',
            subject: $caller['file']
        );
        $class = (new \ReflectionClass($caller['class']))->getShortName();
        array_push($this->dumps, [
            'method' => $class . $caller['type'] . $caller['function'],
            'location' => $caller_location . ":{$caller['line']}",
            'vars' => $vars
        ]);
    }


    public static function dump(mixed ...$var): Debug
    {
        if (self::$instance === null) {
            if (class_exists('Highlight\Highlighter')) {
                $highlighter_style = new StyleAsset('vendor/scrivo/highlight.php/styles/default.css', 'syntax-highlighter', use_in: 'admin');
                $highlighter_style->add_inline('.sticky-header {position:sticky;top:0;background-color:white;}')->enqueue();
            }
            self::$instance = new self();
        }
        add_action(self::$instance->get_action_hook(), [self::$instance, 'echo']);
        self::$instance->add_var(...$var);
        return self::$instance;
    }

    public function echo()
    {
        echo "<{$this->element_tag} class='{$this->element_class} {$this->action_hook}' " . $this->get_style_tag() . ">{$this->get_content()}</pre></{$this->element_tag}>";
    }

    private function get_content(): string
    {
        $content = '';
        foreach ($this->dumps as $dump) {
            $content .= "<div>";
            $content .= "<h2 class='sticky-header'>{$dump['location']}</h2>";
            foreach ($dump['vars'] as $var) {
                $content .= "<div ><p class='sticky-header'>{$var['type']}</p><pre>" . $this::get_syntax_highlighted_code($var['value']) . "</pre></div>";
            }
            $content .= "</div>";
        }
        return $content;
    }

    protected function get_action_hook(): string
    {
        $action_hook = '';
        if (is_admin() && !did_action('in_admin_header')) {
            $action_hook =  'in_admin_header';
        } else {
        }
        return $action_hook;
    }

    private function get_style_tag(array $style = []): string
    {
        $tag = "style='";
        $style = array_merge($this->style, $style);
        foreach ($style as $property => $value) {
            $tag .= "{$property}:{$value};";
        }
        return $tag . "'";
    }

    private static function get_syntax_highlighted_code(string $code): string
    {
        if (!class_exists('Highlight\Highlighter')) {
            return $code;
        }
        $highlighter = new \Highlight\Highlighter();
        $highlighted_code = $highlighter->highlight('php', $code);
        return "<code style='background: none;' class=\"{$highlighted_code->language}\">{$highlighted_code->value}</code>";
    }
}
