<?php

namespace WP_Framework\Database\SQL;

class SyntaxCheck
{
    protected static bool $throws_errors = false;
    private function __construct()
    {
    }

    public static function is_safe_value(string $value)
    {
        if (preg_match('/[\'";]/', $value)) {
            return self::throw_exception("`$value` is not a safe value.");
        }
        return true;
    }


    /**
     * Checks if a given string is a valid SQL field name.
     * For table column names, keys in general...
     *
     * @param string $field_name The name to check.
     *
     * @return bool True if the field name is valid; otherwise, false.
     */
    public static function is_field_name(string $field_name): bool
    {
        if (20 < strlen($field_name)) {
            return self::throw_exception("`$field_name` is an invalid name: too long for a field name.");
        }
        $first_character = $field_name[0];
        if (!ctype_alpha($first_character)) {
            return self::throw_exception("`$field_name` is an invalid name: first character must be [a-Z].");
        }
        foreach (str_split($field_name) as $character) {
            if ((!ctype_alpha($character)) && $character != '_') {
                return self::throw_exception("`$field_name` is an invalid name: character must be [a-Z] or '_'.");
            }
        }
        return true;
    }

    /**
     * Checks if a given string is a valid SQL table name.
     * For table names
     *
     * @param string $table_name The name to check.
     *
     * @return bool True if the table name is valid; otherwise, false.
     */
    public static function is_table_name(string $table_name): bool
    {
        if (20 < strlen($table_name)) {
            return self::throw_exception("`$table_name` is an invalid name: too long for a table name.");
        }
        $first_character = $table_name[0];
        if (!ctype_lower($first_character)) {
            return self::throw_exception("`$table_name` is an invalid name: first character must be [a-z].");
        }
        foreach (str_split($table_name) as $character) {
            if ((!ctype_lower($character)) && $character != '_') {
                return self::throw_exception("`$table_name` is an invalid name: character must be [a-z] or '_'.");
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

        if (!preg_match(
            pattern: '/^(bigint\((\d+)\)|varchar\((\d+)\)|int\((\d+)\)|text|tinytext|datetime)\s*(unsigned)?$/',
            subject: $type
        )) {
            return self::throw_exception("`$type` is an invalid type.");
        }
        return true;
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
            return self::throw_exception("`$type` is not an indexable type.");
        }
        foreach ($matches as $match) {
            if (!$match) continue;
            if ($match === $type) continue;
            if ($match > 20) {
                return self::throw_exception("`$type` is not an indexable type. $match is larger than 20.");
            }
        }
        return true;
    }

    private static function throw_exception(string $message): bool
    {
        if (static::$throws_errors) {
            throw new \Exception($message);
        }
        return false;
    }
}
