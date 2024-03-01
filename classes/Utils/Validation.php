<?php

namespace WP_ThemeFramework\Utils;

class Validation
{
    protected array $accepted_values = [];
    public function __construct(string ...$valid_values)
    {
        $this->accepted_values = $valid_values;
    }

    public function test($string)
    {
        if (!in_array($string, $this->accepted_values)) {
            $var_name = "\$var_name";
            $last_accepted_value = array_pop($this->accepted_values);
            $accepted_values_string = "'" . implode("', '", $this->accepted_values) . "' or '" . $last_accepted_value . "'";
            $error = "'$var_name' is '$string', but should be $accepted_values_string.";
            throw new \Error($error);
        }
    }
}
