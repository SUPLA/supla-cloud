<?php

namespace SuplaBundle\Utils;

final class StringUtils {
    private function __construct() {
    }

    public static function snakeCaseToCamelCase(string $string): string {
        return lcfirst(str_replace('_', '', ucwords(strtolower($string), '_')));
    }
}
