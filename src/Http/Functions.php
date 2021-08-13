<?php

namespace BadCMS\Http;

class Functions
{
    const load = 1;
}

use function BadCMS\Application\app;
use function BadCMS\Router\current_route;

/**
 * Redirects to $url with $status
 *
 * @param       $url
 * @param  int  $status
 */
function redirect($url, $status = 302)
{
    header("Location: $url", true, $status);
    die();
}

/**
 * Get request body
 *
 * @return false|string
 */
function body()
{
    return file_get_contents("php://input");
}

/**
 * Construct request struct
 *
 * @return array
 */
function request()
{
    $request = app('request')();

    if (count($request)) {
        return $request;
    }

    $uri = explode('/', $_SERVER['REQUEST_URI']);
    $uri = array_merge(array_filter($uri));
    $params = $_GET + $_POST;

    return app('request')([
        // Store HTTP request headers
        'headers' => $headers = array_change_key_case(getallheaders()),
        'header' => $header = function ($key, $default = null) use ($headers) {
            return $headers[strtolower($key)] ?? $default;
        },
        "method" => $method = $_SERVER["REQUEST_METHOD"],
        'get' => function ($key, $default = null) use ($params) {
            return $params[$key] ?? $default;
        },
        'all' => function () use ($params) {
            return $params;
        },
        "body" => $body = body(),
        "json" => function () use ($body) {
            if (strlen($body) > 0 && isValidJSON($body)) {
                return json_decode($body, JSON_OBJECT_AS_ARRAY);
            }

            return null;
        },
        "cookies" => $_COOKIE,
        "uri" => $uri,
        "route" => current_route(),
        "wantsJson" => (strpos($header("Accept"), "application/json") !== false),
        "user" => null,
    ]);
}

/**
 * Prepare response to client
 *
 * @param $responseData
 *
 * @return array
 */
function response($responseData)
{
    if (is_callable($responseData)) {
        $responseData = $responseData();
    }

    if (is_string($responseData)) {
        $responseData = [
            'type' => 'html',
            'content' => $responseData,
        ];
    }

    $type = $responseData["type"] ?? "html";

    switch (true) {
        case $type == "json":
            $contentType = "application/json";
            break;
        case $type == "raw":
        case $type == "html":
        case $type == "text":
        default:
            $contentType = "text/html";
    }

    return [
        "headers" => [
            'Content-Type' => $contentType,
        ],
        "content" => $responseData['content'],
    ];
}

/**
 * Build headers
 *
 * @param  array  $headers  HTTP Headers
 *
 * @return array
 */
function headers(array $headers)
{
    $combined = [];
    foreach ($headers as $key => $value) {
        $combined[] = "$key: $value";
    }

    return $combined;
}
