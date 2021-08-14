<?php

namespace BadCMS\Router;

use function BadCMS\Application\app;
use function BadCMS\View\render;
use function BadCMS\Auth\login;
use function BadCMS\Auth\user;
use function BadCMS\Http\redirect;

class Functions
{
    const load = 1;
}

[\BadCMS\Http\Functions::load, \BadCMS\Auth\Functions::load];

/**
 * Загружает основные маршруты приложения
 */
function loadRoutes()
{
    $routes = require app_path("routes/routes.php");
    foreach ($routes as $name => $route) {
        addRoute($name, $route);
    }
}

/**
 * Добавляет маршруты в роутер
 *
 * @param $newRoutes
 *
 * @return mixed
 */
function addRoutes($newRoutes)
{
    if (is_callable($newRoutes)) {
        $newRoutes = $newRoutes();
    }

    foreach ($newRoutes as $key => $route) {
        if (is_callable($route)) {
            $route = [
                'action' => $route,
                'name' => trim(str_replace("/", ".", $key), "/ \r\n\t."),
                'uri' => $key,
            ];
        }

        addRoute($route["uri"] ?? $key, $route["action"] ?? $route, $route["name"] ?? $key);
    }

    return $newRoutes;
}

/**
 * Формирует ссылку на маршрут
 *
 * @param  string  $name    Название маршрута - используется для обращения по имени
 * @param  array   $params  Параметры для построения ссылки
 *                          В префиксе маршрута {variable} - будет заменено на значение
 *                          параметра 'variable' в массиве параметров
 *
 *                          Пример:
 *                          /users/{username} -> /users/FooBar
 *
 * @return string
 */
function route(string $name, $params = []): string
{
    $routes = app('routes');

    $routeName = $name; //trim(str_replace(["/", "_", ","], ".", $name), ".\t\n\r\0\x0B");

    $found = array_filter($routes, function ($route) use ($routeName) {
        return $route['name'] == $routeName || $route['name'] == "/$routeName";
    });

    if (!$found) {
        throw new \Exception("Route `$name` not found", 500);
    }

    $found = array_shift($found);

    $uri = $found['prefix'];

    if (count($params)) {
        foreach ($params as $paramName => $paramValue) {
            $uri = str_replace(['{'.$paramName."}", "{?$paramName}"], $paramValue, $uri);

            if (in_array('{'.$paramName.'}', $found['params'])) {
                unset($params[$paramName]);
            }
        }
    }

    $params = http_build_query($params);
    $uriTpl = "%s&%s";
    $slugged = config("app.use_slugs", false);

    if (!$slugged) {
        $uriTpl = "/?r=".$uriTpl;
    }

    return trim(sprintf($uriTpl, $uri, $params), " &?");
}

function getRouteParams($route)
{
    preg_match_all('/\{([-_\w\d]*)\}/ixU', $route, $matches, PREG_PATTERN_ORDER);;

    return $matches[0];
}

/**
 * Adding route
 *
 * @param  string  $prefix    URI prefix for route
 * @param  null    $callback  Callback for route
 * @param  null    $name      name of route
 */
function addRoute(string $prefix, $callback = null, $name = null)
{
    if ($prefix[0] != '/') {
        $prefix = "/".$prefix;
    }

    $params = getRouteParams($prefix);

    $route = compact('prefix', 'name', 'params');
    $route['action'] = $callback;

    $tokenPatterns = [];
    if (count($params)) {
        foreach ($params as $param) {
            $pattern = '([A-Za-z\d\-_]*)';
            if ($param[1] == '?') {
                $pattern .= "?";
            }
            $tokenPatterns[$param] = $pattern;
        }
    }

    // Define regex pattern for route matching
    $route['pattern'] = "%^".str_replace(array_keys($tokenPatterns), array_values($tokenPatterns), $prefix)."$%i";

    $route['name'] = $name ?? $prefix;

    $app = app();
    $app["router"]["addRoute"]($route);

    return $route;
}

function matchRoute($uri)
{
    $routes = app('routes');

    foreach ($routes as $name => $route) {
        if (!is_callable($route) && isset($route['pattern'])) {
            if (preg_match_all($route['pattern'], "/".$uri, $matches)) {
                array_shift($matches);
                if (isset($route['params']) && count($route['params'])) {
                    $matches = array_map(function ($match) { return $match[0]; }, $matches);
                    $params = array_map(function ($param) { return trim($param, "{}"); }, $route['params']);
                    $route["bind"] = array_combine($params, $matches);
                }
                $route['uri'] = $uri;

                return $route;
            }
        } else {
            if ($uri == $name) {
                return $route;
            }
        }
    }

    return null;
}

function uri()
{
    $r = $_GET["r"] ?? null;

    if (!$r) {
        $uri = $_SERVER["REQUEST_URI"];
//        if( trim($uri) == '/' || substr('/',-1)!==false ){
//
//        }
        $r = trim($uri) == '/' ? 'index' : trim($uri, '/');
    }

    return $r;
}

function current_route()
{
    $uri = uri();

    $route = matchRoute($uri);

    // check route
    if (is_null($route)) {
        flash("error", "Page not found: ".$uri);
    }

    return $route;
}
