<?php

namespace BadCMS\Auth;

class Functions
{
    const load = 1;
}

use function BadCMS\Application\app;
use function BadCMS\Http\redirect;
use function BadCMS\Models\User\findUser;
use function BadCMS\Router\route;

[
    \BadCMS\Models\User\Functions::load,
];

function user()
{
    return app("request")('user');
}

function guest()
{
    return !user();
}

function check()
{
    $user = (isset($_SESSION[SESSION_USER_KEY]) ? unserialize($_SESSION[SESSION_USER_KEY]) : null);

    if (!findUser($user['username'])) {
        unset($_SESSION[SESSION_USER_KEY], $user);
    }

    $request = app("request")(compact('user'));

    return $request['user'] ?? false;
}

function login($request)
{
    $username = $request["get"]('username');
    $password = $request["get"]('password');

    if (!$user = \BadCMS\Models\User\findUser($username)) {
        return false;
    }

    $isLoggedIn = attempt($user, $password);
    if ($isLoggedIn) {
        $_SESSION[SESSION_USER_KEY] = serialize($user);
        app("user", $user);

        flash("message", "Hello, $username");
        session_commit();
        redirect(route('index'));
    }

    return $isLoggedIn;
}

/**
 * Отказ в авторизации действия
 *
 * @param  string|null  $message
 */
function deny($message = null)
{
    $message && flash('message', $message);
    redirect(route('403'), 302);

    return false;
}

/**
 * Проверка пароля пользователя
 *
 * @param $username
 * @param $password
 *
 * @return bool
 */
function attempt($user, $password)
{
    if (empty($user) || !isset($user["password"])) {
        return false;
    }

    return password_verify($password, $user['password']);
}

/**
 * Создание хеша для пароля
 *
 * @param $password
 *
 * @return string
 */
function hashPassword($password)
{
    return password_hash($password, config("auth.hash", PASSWORD_DEFAULT),
        ["cost" => config("auth.cost", 10)]);
}
