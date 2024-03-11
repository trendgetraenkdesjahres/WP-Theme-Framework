<?php

namespace WP_Framework\Debug;


/**
 * Class Debug
 *
 * @package WP_Framework\Debug
 */
class Debug
{
    /**
     * @var Debug|null Singleton instance
     */
    private static $instance;

    /**
     * @var string Element class name
     */
    private static string $element_class = 'fw_debug';

    /**
     * @var string Element tag name
     */
    private static string $element_tag = 'section';


    /**
     * @var array Holds data for variable dumps
     */
    private array $dumps = [];

    /**
     * @var array Holds data for errors
     */
    private array $errors = [];

    /**
     * @var string Holds the action hook of the latest dump/error
     */
    protected string $action_hook = '';

    /**
     * @var string Inline styles for debugging output
     */
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

        & .sticky-header {
            position: sticky;
            top: 0;
            background-color: white;
        }
        & h2.sticky-header {
            border-color: #c3c4c7;
            border-left: 2px solid #72aee6;
            background: #f0f6fc;
            padding: 0 5px;
        }
        & .var-details:not(:last-child) {
            border-bottom: 1px solid #c3c4c7;
            margin-inline: -1rem;
            padding-inline: 1rem;
        }
        & .type {
            margin-inline: -1rem;
            padding-inline: 1rem;
        }
        & .statement {
            border-color: #c3c4c7;
            border-left: 2px solid #72aee6;
            background: #f0f6fc;
            box-shadow: 0 2px 0 rgba(0,0,0,.02), 0 1px 0 rgba(0,0,0,.02);
            & pre {
                margin-block-start: -1rem;
                padding-block-start: 1rem;
            }
            & .accordion-content {
                overflow: hidden;
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
                    margin-top: -1.1rem
            }
        }
    ";

    /**
     * Private constructor to prevent multiple instances (singleton pattern).
     */
    private function __construct()
    {
    }

    /**
     * Initializes the Debug instance and sets up necessary actions and styles.
     *
     * @return Debug The initialized Debug instance
     */
    public static function init(): Debug
    {
        if (self::$instance === null) {
            # add syntax highlighter from composer
            if (class_exists('Highlight\Highlighter')) {
                add_action('admin_enqueue_scripts', function () {
                    wp_enqueue_style(
                        handle: self::$element_class,
                        src: get_stylesheet_directory_uri() . '/inc/framework/vendor/scrivo/highlight.php/styles/default.css'
                    );
                });
            }

            # add styles and scripts
            add_action('admin_enqueue_scripts', function () {
                wp_enqueue_style(self::$element_class, false);
                wp_add_inline_style(
                    handle: self::$element_class,
                    data: "." . self::$element_class . " {" . self::$inline_style . " }"
                );
                wp_enqueue_script(self::$element_class, false);
                wp_add_inline_script('jquery', self::get_inline_script());
            });

            # initate constant self (singleton)
            self::$instance = new self();
        }
        # register custom error display, disable phps error displaying.
        /*         ini_set('display_errors', 0);
        add_filter('wp_die_handler', [self::$instance, 'wp_die_filter']); */
        return self::$instance;
    }

    /**
     * Adds a variable dump to the list for later output.
     *
     * @param mixed ...$var Variables to be dumped
     *
     * @return Debug The Debug instance
     */
    public static function var(mixed ...$var): Debug
    {
        $me = self::init()
            ->add_var(...$var)
            ->update_action_hook();
        add_action($me->action_hook, [$me, 'print_var']);
        return $me;
    }

    /**
     * Outputs a variable dump and terminates the script.
     *
     * @param mixed ...$var Variables to be dumped
     */
    public static function die(mixed ...$var): void
    {
        $me = self::init()
            ->add_var(...$var)
            ->update_action_hook();
        add_action($me->action_hook, [$me, 'print_var']);
        die();
    }

    /**
     * Outputs an error message and terminates the script.
     *
     * @param mixed ...$var Error data
     */
    private static function error(mixed ...$var): void
    {
        $me = self::init()
            ->add_error(...$var)
            ->update_action_hook();
        add_action($me->action_hook, [$me, 'print_var']);
        die();
    }

    /**
     * Gets syntax-highlighted code using the Highlight library.
     *
     * @param string $code The code to be highlighted
     *
     * @return string The highlighted code wrapped in a <code> tag
     */
    protected static function get_syntax_highlighted_code(string $code): string
    {
        if (!class_exists('Highlight\Highlighter')) {
            return $code;
        }
        $highlighter = new \Highlight\Highlighter();
        $highlighted_code = $highlighter->highlight('php', $code);
        return "<code style='background: none;' class=\"{$highlighted_code->language}\">{$highlighted_code->value}</code>";
    }

    /**
     * Generates the inline script for relocating the debug element after the DOM has loaded.
     *
     * @return string The inline relocation script
     */
    private static function get_inline_script(): string
    {
        return "
        document.addEventListener('DOMContentLoaded', function() {
            var debugElement = document.querySelector('." . self::$element_class . "');
            if (debugElement) {
                var wpAdminBar = document.getElementById('wpadminbar');
                wpAdminBar.insertAdjacentElement('afterend', debugElement);
            }
        });
    ";
    }

    /**
     * Provides a custom error handler based on the environment (admin or not).
     *
     * @param callable $die_handler Original die handler
     *
     * @return callable The custom error handler
     */
    public function wp_die_filter(callable $die_handler): callable
    {
        if (is_admin()) {
            return [$this, 'print_error'];
        }
        return [$this, 'print_error_discrete'];
    }

    /**
     * Prints an error message and associated content.
     *
     * @param mixed $wp_error WP_Error object containing error data
     */
    public function print_error($wp_error): void
    {
        # WP_Error->error_data contains an error about all errors so far.
        self::add_error(...$wp_error->error_data);
        echo "<" . self::$element_tag . " class='" . self::$element_class . " {$this->action_hook}'>{$this->get_error_content()}</" . self::$element_tag . ">";
    }

    /**
     * Prints an error message for discrete error handling.
     */
    public function print_error_discrete(): void
    {
        die();
    }

    /**
     * Prints variable-related content.
     */
    public function print_var(): void
    {
        echo "<" . self::$element_tag . " class='" . self::$element_class . " {$this->action_hook}'>{$this->get_var_content()}</" . self::$element_tag . ">";
        return;
    }

    /**
     * Retrieves the content for variable-related debugging output.
     *
     * @return string Variable content
     */
    private function get_var_content(): string
    {
        $content = '';
        foreach ($this->dumps as $dump) {
            $in_action = $dump['while_wp_action'] ? "<em> (currently in: '{$dump['while_wp_action']}')</em>" : '';
            $content .= "<h2 class='sticky-header'>{$dump['short_location']} {$in_action} </h2>" . PHP_EOL;
            $content .= "<div class='var-details'><div class='statement'> {$dump['surrounding_statement']} </div>" . PHP_EOL;
            foreach ($dump['vars'] as $var) {
                $content .= "<div class='type current-hook sticky-header'><strong>{$var['name']}</strong> ({$var['type']})</div>" . PHP_EOL;
                if (in_array($var['type'], ['string', 'int', 'float', 'bool', 'null'])) {
                    $content .= "<div class='value'><pre><code style='background: #f0f0f1;border-radius:5px;'>" . $var['value'] . "</code></pre></div>" . PHP_EOL;
                } else {
                    # if object, get the fancy syntax highlightings
                    $content .= "<div class='value'><pre>" . $this::get_syntax_highlighted_code($var['value']) . "</pre></div>" . PHP_EOL;
                }
            }
            $content .= "</div>";
        }
        return $content;
    }

    /**
     * Retrieves the content for error-related debugging output.
     *
     * @return string Error content
     */
    private function get_error_content(): string
    {
        $content = '';
        foreach ($this->errors as $error) {
            $in_action = $error['while_wp_action'] ? "<em> (currently in: '{$error['while_wp_action']}')</em>" : '';
            $content .= "<h2 class='sticky-header'>{$error['short_location']} {$in_action} </h2>" . PHP_EOL;
            $content .= "<div class='title'>{$error['title']}</div>" . PHP_EOL;
            $content .= "<div class='statement'> {$error['surrounding_statement']} </div>" . PHP_EOL;
            $content .= "<div class='backtrace'>Backtrace:{$error['backtrace']} </div>" . PHP_EOL;
        }
        return $content;
    }

    /**
     * Adds variable-related information to the dumps array.
     *
     * @param mixed ...$var Variables to be dumped
     *
     * @return Debug The Debug instance
     */
    protected function add_var(mixed ...$var): Debug
    {
        $dump_info = $this->get_var_call_information();
        $dump_info['vars'] = $this->get_variable_info($dump_info['file_path'], $dump_info['file_line'], ...$var);

        # pushes info about the Debug call and it's vars into the dumps array
        array_push($this->dumps, $dump_info);
        return $this;
    }


    /**
     * Adds error information to the errors array.
     *
     * @param mixed ...$wp_error_data Error data of WP_Error object
     *
     * @return Debug The Debug instance
     */
    protected function add_error(...$wp_error_data): Debug
    {
        foreach ($wp_error_data as $error_data) {
            $error = $error_data['error'];
            $error_info = $this->get_error_information($error['file'], $error['line']);
            $error_info['backtrace'] = $this->get_backtrace_from_error_message($error['message'], true);
            $error_info['title'] = $this->get_title_from_error_message($error['message'], true);
            # pushes info about the Error.
            array_push($this->errors, $error_info);
        }
        return $this;
    }

    /**
     * Updates the current action hook in the Debug instance.
     *
     * @return Debug The Debug instance
     */
    private function update_action_hook(): Debug
    {
        if (is_admin()) {
            $hooks = [
                /*                 'in_admin_header',
                'admin_enqueue_scripts',
                'admin_init',
                'admin_menu',
                'admin_notices',
                'admin_footer',
                'admin_bar_menu',
                'admin_footer-text', */
                'shutdown'
            ];
        } else {
            $hooks = [
                /*                 'after_setup_theme',
                'wp_enqueue_scripts',
                'wp_body_open',
                'wp_footer', */
                'shutdown'
            ];
        }
        foreach ($hooks as $hook) {
            if (!did_action($hook)) {
                $this->action_hook = $hook;
                break;
            }
        }
        return $this;
    }

    /**
     * Retrieves information about the call that added a variable dump.
     *
     * @return array Information about the variable dump call
     */
    private function get_var_call_information(): array
    {
        $caller = debug_backtrace(limit: 3)[2];
        $short_location = str_replace(
            search: get_stylesheet_directory(),
            replace: '',
            subject: $caller['file']
        ) . ":{$caller['line']}";
        $class = (new \ReflectionClass($caller['class']))->getShortName();

        return [
            'debug_class' => $class,
            'debug_method' => $caller['function'],
            'debug_call_expr' => "{$class}{$caller['type']}{$caller['function']}",
            'file_path' => $caller['file'],
            'file_line' => $caller['line'],
            'short_location' => $short_location,
            'while_wp_action' => current_filter(),
            'surrounding_statement' => $this->get_surrounding_statement($caller['file'], $caller['line'], true, true)
        ];
    }

    /**
     * Retrieves information about an error based on file and line.
     *
     * @param string $file File path
     * @param int    $line Line number
     *
     * @return array Information about the error
     */
    private function get_error_information(string $file, int $line): array
    {
        $short_location = str_replace(
            search: get_stylesheet_directory(),
            replace: '',
            subject: $file
        ) . ":{$line}";
        return [
            'file_path' => $file,
            'file_line' => $line,
            'short_location' => "$short_location",
            'while_wp_action' => current_filter(),
            'surrounding_statement' => $this->get_surrounding_statement($file, $line, true, true)
        ];
    }

    /**
     * Retrieves backtrace information from an error message.
     *
     * @param string $message              Error message
     * @param bool   $use_syntax_html_markup Whether to use syntax highlighting HTML markup
     *
     * @return string Backtrace information
     */
    private function get_backtrace_from_error_message(string $message, bool $use_syntax_html_markup): string
    {
        $message_backtrace = explode(PHP_EOL . 'Stack trace:' . PHP_EOL, $message)[1];
        $backtrace = "<ol>";

        foreach (explode(PHP_EOL, $message_backtrace) as $trace_line) {
            if ($use_syntax_html_markup) {
                $trace_line = preg_replace('/#\d+\s+/', '', $trace_line);
                $trace_line = self::get_syntax_highlighted_code($trace_line);
            }
            $backtrace .= "<li><code>{$trace_line}</code></li>";
            if (is_int(strpos($trace_line, '{main}'))) break;
        }
        $backtrace .= "</ol>";
        return $backtrace;
    }

    /**
     * Retrieves the title from an error message.
     *
     * @param string $message Error message
     *
     * @return string Error title
     */
    private function get_title_from_error_message(string $message): string
    {
        $message = preg_replace('/in\w.+:\d+\n/', 'AWSXDRF', $message);
        $title = explode('AWSXDRF', $message)[0];
        return $title;
    }

    /**
     * Retrieves information about variables based on file, line, and values.
     *
     * @param string $file File path
     * @param int    $line Line number
     * @param mixed  ...$var Variables to be analyzed
     *
     * @return array Variable information
     */
    private function get_variable_info(string $file, int $line, mixed ...$var): array
    {
        $var_names = $this->get_var_names($file, $line);
        $variables = [];
        foreach ($var as $i => $value) {
            array_push($variables, [
                'type' => ($type = gettype($value)) == 'object' ? get_class($value) : $type,
                'value' => print_r($value, true),
                'name' => $var_names[$i] ?? null
            ]);
        }
        return $variables;
    }

    /**
     * Retrieves variable names from the Debug::var() call in the source code.
     *
     * @param string $file File path
     * @param int    $line Line number
     *
     * @return array Variable names
     */
    private function get_var_names(string $file, int $line): array
    {
        $var_names = [];
        $line = array_slice(file($file), $line - 1)[0];
        preg_match('/Debug::var\((.*)\);/', $line, $matches);
        if (!$matches) {
            return $var_names;
        }
        $var_names = explode(',', $matches[1]);
        $var_names = array_filter($var_names, function ($element) {
            return trim($element);
        });
        return $var_names;
    }

    /**
     * Retrieves the surrounding statement of a specific line in a file.
     *
     * @param string $file                    File path
     * @param int    $line                    Line number
     * @param bool   $use_syntax_html_markup  Whether to use syntax highlighting HTML markup
     * @param bool   $use_accordion           Whether to use an accordion element for long code
     *
     * @return string Surrounding statement
     */
    private function get_surrounding_statement(string $file, int $line, bool $use_syntax_html_markup = false, bool $use_accordion = false): string
    {
        # an offset of 1 is exactly the line the command came from. (arrays starting with 0, lines of a document with 1).
        $line_offset = 2;

        $code_lines = array_slice(file($file), $line - $line_offset);
        $code = '';

        # remove empty lines at the beginning
        foreach ($code_lines as $i => $code_line) {
            if (!$code_line) array_shift($code_lines);
        }

        foreach ($code_lines as $i => $code_line) {
            $added_lines = $i + 1;
            $document_line = $line + $i - 1;
            # if the counter already was successfull (all brackets zero), it gets set to false. but maybe we want to see more lines (offset not reached.)
            if (!isset($open_brackets_counter) || !$open_brackets_counter) {
                $open_brackets_counter = [
                    'curly' => 0,
                    'round' => 0,
                    'square' => 0,
                ];
            }
            if ($use_syntax_html_markup) {
                $code_line = self::get_syntax_highlighted_code(trim($code_line, PHP_EOL));
            }
            $code .= "$document_line " . $code_line . PHP_EOL;
            $open_brackets_counter = self::update_brackets_counter($open_brackets_counter, $code_line);
            if ($open_brackets_counter === false && $added_lines > $line_offset - 1) break;
        }

        # wrap the code into a accordeon element, which hides longer codes.
        if ($use_accordion) {
            $code = $this->get_accordion_wrap(trim($code, PHP_EOL));
        }
        return trim($code);
    }

    /**
     * Wraps code in an accordion element for hiding longer codes.
     *
     * @param string $code Code to be wrapped
     *
     * @return string Code wrapped in an accordion element
     */
    private function get_accordion_wrap(string $code): string
    {
        $lines_accordeon_collapsed = 4;
        $code_lines = explode(PHP_EOL, trim($code));
        $random = bin2hex(random_bytes(2));

        # if code is too short to add accordion
        if ($lines_accordeon_collapsed >= count($code_lines) - 1) {
            return "<pre>" . PHP_EOL . "<p class='code content'>{$code}</p>" . PHP_EOL . "</pre>";
        }

        $code = "<pre class='accordion-content content'>" . PHP_EOL . "<input type='checkbox' class='code'  id='dump-{$random}'>" . PHP_EOL . "<label class='code' for='dump-{$random}'>";
        foreach ($code_lines as $i => $content) {
            # arrays start with 0, document-lines with 1
            $line = $i + 1;

            # append content
            $code .= $content;

            # close label, open hidden accordeon element
            if ($line === $lines_accordeon_collapsed) {
                $code .= "</label><p class='code'>";
            }

            # append linebreak
            $code .= PHP_EOL;
        }

        # return with closed accordeon element
        return "{$code}</p></pre>";
    }

    /**
     * Updates the open brackets counter based on a line of code.
     *
     * @param array  $open_brackets_counter Array containing counts of open brackets
     * @param string $line                  Line of code
     *
     * @return array|false Updated open brackets counter or false if all brackets are closed
     */
    private static function update_brackets_counter(array $open_brackets_counter, $line): array|false
    {
        $open_brackets_counter['curly'] += substr_count($line, '{');
        $open_brackets_counter['round'] += substr_count($line, '(');
        $open_brackets_counter['square'] += substr_count($line, '[');

        $open_brackets_counter['curly'] -= substr_count($line, '}');
        $open_brackets_counter['round'] -= substr_count($line, ')');
        $open_brackets_counter['square'] -= substr_count($line, ']');
        if (
            $open_brackets_counter['curly'] === 0 &&
            $open_brackets_counter['round'] === 0 &&
            $open_brackets_counter['square'] === 0
        ) {
            return false;
        }
        return $open_brackets_counter;
    }
}
