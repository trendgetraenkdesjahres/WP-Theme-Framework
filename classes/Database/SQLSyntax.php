<?php

namespace WP_Framework\Database;

class SQLSyntax
{
    /**
     * Checks if a given string is a valid SQL field name.
     *
     * @param string $field_name The table name to check.
     *
     * @return bool True if the field name is valid; otherwise, false.
     */
    public static function is_field_name(string $field_name): bool
    {
        if (20 < strlen($field_name)) {
            return false;
        }
        $first_character = $field_name[0];
        if (!ctype_lower($first_character)) {
            return false;
        }
        foreach (str_split($field_name) as $character) {
            if ((!ctype_lower($character)) && $character != '_') {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks if a given string is a valid SQL data type.
     *
     * @param string $type The SQL data type to check.
     *
     * @return bool True if the data type is valid; otherwise, false.
     */
    public static function is_data_type(string $type): bool
    {
        return (bool) preg_match(
            pattern: '/^(bigint\((\d+)\)|varchar\((\d+)\)|int\((\d+)\)|text|tinytext|datetime)\s*(unsigned)?$/',
            subject: $type
        );
    }

    /**
     * Checks if a given SQL data type is indexable.
     *
     * @param string $type The SQL data type to check.
     *
     * @return bool True if the data type is indexable; otherwise, false.
     */
    public static function is_indexable_data_type(string $type): bool
    {
        $matches = [];
        if (!preg_match(
            pattern: '/^(bigint\((\d+)\)|varchar\((\d+)\)|int\((\d+)\)|datetime)\s*(unsigned)?$/',
            subject: $type,
            matches: $matches
        )) {
            return false;
        }
        foreach ($matches as $match) {
            if (!$match) continue;
            if ($match === $type) continue;
            if ($match > 20) {
                return false;
            }
        }
        return true;
    }

    public static function get_type_info(string $type): array
    {
        $matches = [];
        preg_match(
            pattern: '/^(\w+)(\(\d+\))?/',
            subject: $type,
            matches: $matches
        );
        return [
            'type' => $matches[1],
            'size' => isset($matches[2]) ? trim($matches[2], '()') : null
        ];
    }
}
