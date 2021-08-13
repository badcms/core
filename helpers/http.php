<?php

/**
 * Run action
 *
 * @param  callable  $action
 * @param  array     $params
 *
 * @return mixed
 */
function runAction(callable $action, $params = [])
{
    $request = array_shift($params);

    $bindedParams = $request['route']['bind'] ?? [];

    return $action($request, ...array_values($bindedParams + $params));
}

/**
 * Set or Get flash message with $key
 *
 * @param  string  $key
 * @param  null    $message
 *
 * @return false|mixed
 */
function flash($key = "message", $message = null)
{
    if ($message) {
        $_SESSION["flash"][$key] = $message;
    } else {
        $message = $_SESSION["flash"][$key] ?? null;
        if ($message) {
            unset($_SESSION["flash"][$key]);
        }
    }

    return $message ?? false;
}

function hasFlash()
{
    return isset($_SESSION["flash"]) && count($_SESSION["flash"]);
}
