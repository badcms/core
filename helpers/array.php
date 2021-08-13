<?php

/**
 * Set an array item to a given value using "dot" notation.
 *
 * If no key is given to the method, the entire array will be replaced.
 *
 * @param  array   $array
 * @param  string  $key
 * @param  mixed   $value
 *
 * @return array
 */
function array_dot_set(&$array, $key, $value)
{
    if (is_null($key)) {
        return $array = $value;
    }

    $keys = explode('.', $key);

    while (count($keys) > 1) {
        $key = array_shift($keys);

        // If the key doesn't exist at this depth, we will just create an empty array
        // to hold the next value, allowing us to create the arrays to hold final
        // values at the correct depth. Then we'll keep digging into the array.
        if (!isset($array[$key]) || !is_array($array[$key])) {
            $array[$key] = [];
        }

        $array = &$array[$key];
    }

    $array[array_shift($keys)] = $value;

    return $array;
}

/**
 * Get an item from an array using "dot" notation.
 *
 * @param  \ArrayAccess|array  $array
 * @param  string              $key
 * @param  mixed               $default
 *
 * @return mixed
 */
function array_dot_get($array, $key, $default = null)
{
    if (!array_dot_accessible($array)) {
        return value($default);
    }
    if (is_null($key)) {
        return $array;
    }
    if (array_dot_exists($array, $key)) {
        return $array[$key];
    }
    if (strpos($key, '.') === false) {
        return $array[$key] ?? value($default);
    }
    foreach (explode('.', $key) as $segment) {
        if (array_dot_accessible($array) && array_dot_exists($array, $segment)) {
            $array = $array[$segment];
        } else {
            return value($default);
        }
    }

    return $array;
}

/**
 * Determine whether the given value is array accessible.
 *
 * @param  mixed  $value
 *
 * @return bool
 */
function array_dot_accessible($value)
{
    return is_array($value) || $value instanceof ArrayAccess;
}

/**
 * Determine if the given key exists in the provided array.
 *
 * @param  \ArrayAccess|array  $array
 * @param  string|int          $key
 *
 * @return bool
 */
function array_dot_exists($array, $key)
{
    if ($array instanceof ArrayAccess) {
        return $array->offsetExists($key);
    }

    return array_key_exists($key, $array);
}
