<?php

namespace WP_Framework\Debug;

use WP_Framework\AssetFile\StyleAsset;

class Debug
{

    private static $instance;

    private static string $element_class = 'fw_debug';
    private static string $element_tag = 'section';

    private array $dumps = [];

    protected string $action_hook = '';

    private static string $inline_style = "
    border: 1px solid #c3c4c7;
    background: white;
    border-radius: 0 0 4px 4px;
    padding-inline: 1rem;
    height: fit-content;
    max-height: calc(100vh - 46px);
    overflow: auto;
    margin-right: 1rem;
    resize: vertical;

    .sticky-header {
        position: sticky;
        top: 0;
        background-color: white;
    }
    .accordion-content {
        overflow: hidden;
        border-color: #c3c4c7;
        border-left: 2px solid #72aee6;
        background: #f0f6fc;
        box-shadow: 0 2px 0 rgba(0,0,0,.02), 0 1px 0 rgba(0,0,0,.02);
        & input[type=checkbox] {
            display: none;
        }
        &:has(input[type=checkbox]:checked)  > p.code {
            max-height: calc(100vh - 46px);
        }
        & .code, & code {
            unicode-bidi: unset;
        }
        & > p.code {
                max-height: 0;
                transition: max-height 500ms;
                margin: 0;
            }
    }
    ";

    # private do disable creating of instances (singleton pattern). but ised to init.
    private function __construct()
    {
    }

    public static function init()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        if (class_exists('Highlight\Highlighter')) {
            $highlighter_style = new StyleAsset('vendor/scrivo/highlight.php/styles/default.css', 'syntax-highlighter', use_in: 'admin');
            $highlighter_style->add_inline("." . self::$element_class . " {" . self::$inline_style . " }")->enqueue();
        }
    }

    protected function add_var(mixed ...$var)
    {
        $i = count($this->dumps) - 1;

        $caller = debug_backtrace(limit: 2)[1];
        $caller_location = str_replace(
            search: getcwd() . "/",
            replace: '',
            subject: $caller['file']
        );
        $class = (new \ReflectionClass($caller['class']))->getShortName();

        $var_names = $this->get_var_names($caller['file'], $caller['line'] - 1);
        $vars = [];
        foreach ($var as $i => $var) {
            array_push($vars, [
                'type' => ($type = gettype($var)) == 'object' ? get_class($var) : $type,
                'value' => print_r($var, true),
                'name' => $var_names[$i] ?? null
            ]);
        }

        array_push($this->dumps, [
            'method' => $class . $caller['type'] . $caller['function'],
            'in_action' => current_filter(),
            'location' => $caller_location . ":{$caller['line']}",
            'statement' => $this->get_trimmed_statement($caller['file'], $caller['line'], $i),
            'vars' => $vars
        ]);
    }


    public static function dump(mixed ...$var): Debug
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        self::$instance->add_var(...$var);
        add_action(self::$instance->get_action_hook(), [self::$instance, 'echo']);
        return self::$instance;
    }

    public function echo()
    {
        echo "<" . self::$element_tag . " class='" . self::$element_class . " {$this->action_hook}'>{$this->get_content()}</" . self::$element_tag . ">";
    }

    private function get_content(): string
    {
        $content = '';
        foreach ($this->dumps as $dump) {
            $in_action = $dump['in_action'] ? "<em> (currently in: '{$dump['in_action']}')</em>" : '';
            $content .= "<h2 class='sticky-header'>{$dump['location']} {$in_action} </h2>";
            $content .= "<div class='statement'> <pre class='accordion-content'> {$dump['statement']} </pre> </div>";
            foreach ($dump['vars'] as $var) {
                $content .= "<div class='type current-hook sticky-header'><strong>{$var['name']}</strong> ({$var['type']})</div>";
                $content .= "<div class='value'><pre>" . $this::get_syntax_highlighted_code($var['value']) . "</pre></div>";
            }
        }
        return $content;
    }

    protected function get_action_hook(): string
    {
        if (is_admin()) {
            $hooks = [
                'in_admin_header',
                'admin_enqueue_scripts',
                'admin_init',
                'admin_menu',
                'admin_notices',
                'admin_footer',
                'admin_bar_menu',
                'admin_footer-text',
                'shutdown'
            ];
        } else {
            $hooks = [
                'after_setup_theme',
                'wp_enqueue_scripts',
                'wp_head',
                'wp_footer',
                'shutdown'
            ];
        }
        foreach ($hooks as $hook) {
            if (!did_action($hook)) {
                return $hook;
            }
        }
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

    private function get_var_names(string $file, int $line): array
    {
        $var_names = [];
        $line = array_slice(file($file), $line)[0];
        preg_match('/Debug::dump\((.*)\);/', $line, $matches);
        if (!$matches) {
            return $var_names;
        }
        $var_names = explode(',', $matches[1]);
        $var_names = array_filter($var_names, function ($element) {
            return trim($element);
        });
        return $var_names;
    }

    private function get_trimmed_statement(string $file, int $line, int $dump_index)
    {
        $placeholder = 'Debug::dump()';
        $lines = array_slice(file($file), $line - 2);

        $open_curly_brackets = 0;
        $open_round_brackets = 0;
        $open_square_brackets = 0;
        $code = "<input class='code' type='checkbox' id='dump-{$dump_index}'><label class='code' for='dump-{$dump_index}'>";

        // Iterate through the lines until the specified line number
        for ($i = 0; $i < count($lines); $i++) {

            $lines[$i] = preg_replace('/Debug::dump\([^;]*\);/', $placeholder, $lines[$i]);
            if ($i === 2 || $i === count($lines)) {
                $code .= "" . $this::get_syntax_highlighted_code($lines[$i]) . "</label><p class='code'>";
            } else {
                $code .= $this::get_syntax_highlighted_code($lines[$i]);
            }

            // Count open brackets
            $open_curly_brackets += substr_count($lines[$i], '{');
            $open_round_brackets += substr_count($lines[$i], '(');
            $open_square_brackets += substr_count($lines[$i], '[');


            // Subtract closed brackets
            $open_curly_brackets -= substr_count($lines[$i], '}');
            $open_round_brackets -= substr_count($lines[$i], ')');
            $open_square_brackets -= substr_count($lines[$i], ']');
            // Check if all brackets are closed
            if ($open_curly_brackets == 0 && $open_round_brackets == 0 && $open_square_brackets == 0 && trim($code) != $placeholder) {
                break;
            }
        }


        // Output the extracted code
        return trim($code) . "</p>";
    }
}
