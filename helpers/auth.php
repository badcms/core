<?php

function can($action)
{
    $user = \BadCMS\Http\request()['user'] ?? false;

    $permissions = config('auth.permissions', []);

    if ($user && $permissions && isset($permissions[$action])) {
        return hasRole($permissions[$action], $user);
    }

    return $user && hasRole("admin", $user);
}

function hasRole($role, $user = null)
{
    if (is_string($role)) {
        if ($role == '*') {
            return true;
        }

        $role = [$role];
    }

    return $user && (in_array('*', $role) || in_array($user['role'], $role));
}
