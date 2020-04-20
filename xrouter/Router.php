<?php

if (session_status() == PHP_SESSION_NONE)
    session_start();

include_once "Route.php";

const ROUTE_PUBLIC  = "public";
const ROUTE_PRIVATE = "private";

class Router
{
    /** @var Route[] $routes_any */
    public static $routes_any = [];
    /** @var Route[] $routes_get */
    public static $routes_get = [];
    /** @var Route[] $routes_post */
    public static $routes_post = [];

    public static function get(string $url, $callback, $access = ROUTE_PUBLIC, ...$regexp)
    {
        array_push(Router::$routes_get, new Route($url, $callback, $access, ...$regexp));
    }

    public static function getany(array $urls, $callback, $access = ROUTE_PUBLIC, ...$regexp)
    {
        foreach ($urls as $url)
            array_push(Router::$routes_get, new Route($url, $callback, $access, ...$regexp));
    }

    public static function post(string $url, $callback, $access = ROUTE_PUBLIC, ...$regexp)
    {
        array_push(Router::$routes_post, new Route($url, $callback, $access, ...$regexp));
    }

    public static function postany(array $urls, $callback, $access = ROUTE_PUBLIC, ...$regexp)
    {
        foreach ($urls as $url)
            array_push(Router::$routes_post, new Route($url, $callback, $access, ...$regexp));
    }

    public static function any(string $url, $callback, $access = ROUTE_PUBLIC, ...$regexp)
    {
        array_push(Router::$routes_any, new Route($url, $callback, $access, ...$regexp));
    }

    public static function anyany(array $urls, $callback, $access = ROUTE_PUBLIC, ...$regexp)
    {
        foreach ($urls as $url)
            array_push(Router::$routes_any, new Route($url, $callback, $access, ...$regexp));
    }

    public static function start($access = ROUTE_PUBLIC)
    {
        $requested = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];

        echo Router::do($requested, $method, $access);
    }

    public static function fastbackswitch()
    {
        if (!isset($_SESSION['_previous_route']))
            return;
        header("Location: " . $_SESSION['_previous_route']);
    }

    public static function get_current_route()
    {
        return $_SESSION['_current_route'];
    }

    private static function set_current_route(string $url)
    {
        $_SESSION['_current_route'] = $url;
    }

    private static function set_previous_route()
    {
        $_SESSION['_previous_route'] = $_SESSION['_current_route'];
    }

    public static function do(string $url, string $method, $access = ROUTE_PUBLIC)
    {
        Router::set_previous_route();
        switch ($method) {
            case "GET": {
                    foreach (Router::$routes_get as $route) {
                        $match = $route->match($url);
                        if ($match !== false && $route->accessLevel == $access) {
                            $callback = $route->callback;
                            Router::set_current_route($url);
                            return $callback(...$match);
                        }
                    }
                    foreach (Router::$routes_any as $route) {
                        $match = $route->match($url);
                        if ($match !== false && $route->accessLevel == $access) {
                            $callback = $route->callback;
                            Router::set_current_route($url);
                            return $callback(...$match);
                        }
                    }
                    break;
                }
            case "POST": {
                    foreach (Router::$routes_post as $route) {
                        $match = $route->match($url);
                        if ($match !== false && $route->accessLevel == $access) {
                            $callback = $route->callback;
                            Router::set_current_route($url);
                            return $callback(...$match);
                        }
                    }
                    foreach (Router::$routes_any as $route) {
                        $match = $route->match($url);
                        if ($match !== false && $route->accessLevel == $access) {
                            $callback = $route->callback;
                            Router::set_current_route($url);
                            return $callback(...$match);
                        }
                    }
                    break;
                }
            default: {
                    foreach (Router::$routes_any as $route) {
                        $match = $route->match($url);
                        if ($match !== false && $route->accessLevel == $access) {
                            $callback = $route->callback;
                            Router::set_current_route($url);
                            return $callback(...$match);
                        }
                    }
                    break;
                }
        }

        http_response_code(404);
        return "Not found";
    }

    public static function fastswitch(string $url)
    {
        header("Location: $url");
        return ""; // might be useful for routes
    }

    public static function fastpost(string $url, ...$args)
    {
        $_POST = $args;
        header("Location: $url");
        return ""; // might be useful for routes
    }
    
    public static function fastget(string $url, ...$args)
    {
        $_GET = $args;
        header("Location: $url");
        return ""; // might be useful for routes
    }
}
