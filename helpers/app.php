<?php

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     *
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (!function_exists('config')) {
    /**
     * Получить из конфига значение
     *
 * @param        $key
 * @param  null  $default
 *
 * @return array|ArrayAccess|mixed
 */
function config($key, $default = null)
    {
        return array_dot_get(\BadCMS\Application\app("config"), $key, $default);
    }
}

if (!function_exists('dd')) {
    /**
     * Dump & Die
     *
 * @param  mixed  ...$data
 */
function dd(...$data)
{
    echo "<pre>";
    foreach ($data as $item) {
        var_dump($item);
        }
        die();
    }
}

if (!function_exists('isValidJSON')) {
    /**
     * Check if provided string is valid JSON
     *
 * @param $str
 *
 * @return bool
 */
function isValidJSON($str)
{
    json_decode($str);

        return json_last_error() == JSON_ERROR_NONE;
    }
}

