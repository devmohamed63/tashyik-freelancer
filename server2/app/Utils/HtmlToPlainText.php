<?php

namespace App\Utils;

final class HtmlToPlainText
{
    public static function convert(null|string|array $value): null|string|array
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return array_map(function ($v) {
                return is_string($v) ? self::convertString($v) : $v;
            }, $value);
        }

        return self::convertString($value);
    }

    public static function convertString(string $html): string
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }
}
