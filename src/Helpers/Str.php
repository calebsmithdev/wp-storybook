<?php

namespace WpStorybook\Helpers;

/**
 * @package WpStorybook
 * @since 1.0
 */
class Str
{
    public static function isNullOrEmptyString($str)
    {
        return ($str === null || trim($str) === '');
    }

    public static function contains($haystack, $needle)
    {
        return strpos($haystack, $needle) !== false;
    }

    public static function upperCamelCase($str, array $noStrip = [])
    {
        // non-alpha and non-numeric characters become spaces
        $str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
        $str = trim($str);
        // uppercase the first character of each word
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);

        return $str;
    }
}
