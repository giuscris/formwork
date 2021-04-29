<?php

namespace Formwork\Utils;

use Formwork\Utils\Interpolator\Interpolator;

class Str
{
    /**
     * Translate map to make slugs
     *
     * @var array
     */
    protected const SLUG_TRANSLATE_MAP = [
        '\t' => '', '\r' => '', '!' => '', '"' => '', '#' => '', '$' => '', '%' => '', '\'' => '-', '(' => '', ')' => '', '*' => '', '+' => '', ',' => '', '.' => '', ':' => '', ';' => '', '<' => '', '=' => '', '>' => '', '?' => '', '@' => '', '[' => '', ']' => '', '^' => '', '`' => '', '{' => '', '|' => '', '}' => '', '¡' => '', '£' => '', '¤' => '', '¥' => '', '¦' => '', '§' => '', '«' => '', '°' => '', '»' => '', '‘' => '', '’' => '', '“' => '', '”' => '', '\n' => '-', ' ' => '-', '-' => '-', '–' => '-', '—' => '-', '/' => '-', '\\' => '-', '_' => '-', '~' => '-', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'Ae', 'Ç' => 'C', 'Ð' => 'D', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Œ' => 'Oe', 'Š' => 'S', 'Þ' => 'Th', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'ae', 'å' => 'a', 'æ' => 'ae', '¢' => 'c', 'ç' => 'c', 'ð' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'oe', 'ø' => 'o', 'œ' => 'oe', 'š' => 's', 'ß' => 'ss', 'þ' => 'th', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'ue', 'ý' => 'y', 'ÿ' => 'y', 'Ÿ' => 'y'
    ];

    /**
     * Regex used to interpolate values in strings
     *
     * @var string
     */
    protected const INTERPOLATION_REGEX = '/(\\\\?)\$\{((?:[^{}\\\\]|\\\\.)*)\}/';

    /**
     * Return whether $haystack string starts with $needle
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        return $needle === '' || substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
    }

    /**
     * Return whether $haystack string ends with $needle
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        return $needle === '' || substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }

    /**
     * Return whether $haystack string contains $needle (the empty string is always contained)
     */
    public static function contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }

    /**
     * Return the portion of $haystack before the first occurrence of $needle
     */
    public static function before(string $haystack, string $needle): string
    {
        if ($needle === '') {
            return $haystack;
        }
        $position = strpos($haystack, $needle);
        return $position !== false ? substr($haystack, 0, $position) : $haystack;
    }

    /**
     * Return the portion of $haystack before the last occurrence $needle
     */
    public static function beforeLast(string $haystack, string $needle): string
    {
        if ($needle === '') {
            return $haystack;
        }
        $position = strrpos($haystack, $needle);
        return $position !== false ? substr($haystack, 0, $position) : $haystack;
    }

    /**
     * Return the portion of $haystack after the first occurrence of $needle
     */
    public static function after(string $haystack, string $needle): string
    {
        if ($needle === '') {
            return $haystack;
        }
        $position = strpos($haystack, $needle);
        return $position !== false ? substr($haystack, $position + strlen($needle)) : $haystack;
    }

    /**
     * Return the portion of $haystack after the last occurrence of $needle
     */
    public static function afterLast(string $haystack, string $needle): string
    {
        if ($needle === '') {
            return $haystack;
        }
        $position = strrpos($haystack, $needle);
        return $position !== false ? substr($haystack, $position + strlen($needle)) : $haystack;
    }

    /**
     * Escape HTML tags, quotes and ampersands from a given string
     */
    public static function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_COMPAT | ENT_SUBSTITUTE, 'utf-8', false);
    }

    /**
     * Escape quotes and ampersands from a given string (to be used with HTML attributes)
     */
    public static function escapeAttr(string $string): string
    {
        return str_replace(['&lt;', '&gt;'], ['<', '>'], static::escape($string));
    }

    /**
     * Remove HTML tags and entities from a given string
     */
    public static function removeHTML(string $string): string
    {
        return html_entity_decode(strip_tags($string), ENT_QUOTES | ENT_HTML5, 'utf-8');
    }

    /**
     * Make slug from a given string
     */
    public static function slug(string $string): string
    {
        return preg_replace(['/^-|-$|[^a-z0-9-]/', '/-+/'], ['', '-'], strtr(strtolower($string), self::SLUG_TRANSLATE_MAP));
    }

    /**
     * Append a suffix to a given string if missing
     */
    public static function append(string $string, string $suffix): string
    {
        return static::endsWith($string, $suffix) ? $string : $string . $suffix;
    }

    /**
     * Prepend a prefix to a given string if missing
     */
    public static function prepend(string $string, string $prefix): string
    {
        return static::startsWith($string, $prefix) ? $string : $prefix . $string;
    }

    /**
     * Wrap a string with another
     */
    public static function wrap(string $string, string $wrap): string
    {
        return static::append(static::prepend($string, $wrap), $wrap);
    }

    /**
     * Remove $needle from $haystack if it is at the beginning, otherwise return $haystack
     */
    public static function removeStart(string $haystack, string $needle): string
    {
        return static::startsWith($haystack, $needle) ? substr($haystack, strlen($needle)) : $haystack;
    }

    /**
     * Remove $needle from $haystack if it is at the end, otherwise return $haystack
     */
    public static function removeEnd(string $haystack, string $needle): string
    {
        return static::endsWith($haystack, $needle) ? substr($haystack, 0, -strlen($needle)) : $haystack;
    }

    /**
     * Interpolate variables from $vars array using `${variable}` syntax
     */
    public static function interpolate(string $string, array $vars): string
    {
        return preg_replace_callback(
            self::INTERPOLATION_REGEX,
            static function (array $matches) use ($vars): string {
                [$match, $escape, $value] = $matches;
                if ($escape !== '') {
                    return substr($match, 1);
                }
                $value = str_replace(['\\{', '\\}'], ['{', '}'], html_entity_decode($value));
                return Interpolator::interpolateString($value, $vars);
            },
            $string
        );
    }
}
