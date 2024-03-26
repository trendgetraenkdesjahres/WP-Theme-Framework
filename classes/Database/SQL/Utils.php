<?php

namespace WP_Framework\Database\SQL;

class Utils
{
    private function __construct()
    {
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
