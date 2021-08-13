<?php

namespace BadCMS\PluginManager;

use function BadCMS\Application\app;
use function BadCMS\Router\addRoutes;
use function BadCMS\Storage\readPHP;

class Functions
{
    const load = 1;
}

function loadPlugins()
{
    $plugins = readPHP(config_path('plugins.php'));

    $providers = [];

    // Инициализируем плагины
    foreach ($plugins as $key => $_plugin) {
        $enabled = $_plugin["enabled"] ?? true;
        if ($enabled) {
            $pluginNS = $_plugin["namespace"];
            if (class_exists($pluginNS.'Functions')) {
                $plugin = "\\".$pluginNS.'Functions';
                // Инициализируем плагин и получаем информацию о нём
                $info = $plugin::init($_plugin) + ["enabled" => true];
                $plugins[$key] = $info;

                // Загружаем провайдеры из секции "provides" плагина
                $_providers = $info["provides"] ?? [];
                foreach ($_providers as $provider => $data) {
                    $providers[$provider][] = $data;
                }
            } else { // Если пространство имен плагина не определено - сохраняем ошибку
                $plugins[$key]["enabled"] = false;
                $plugins[$key]["error"] = "Class not found `{$pluginNS}Functions`";
            }
        }
    }

    // В первую очередь загружаем маршруты для роутера, которые могут потребоваться другим провайдерам
    if (isset($providers['routes'])) {
        $routes = $providers["routes"];
        unset($providers["routes"]);

        array_map(function ($routes) {
            addRoutes(is_callable($routes) ? $routes() : $routes);
        }, $routes);
    }


    foreach ($providers as $provider => $data) {
        $data = is_callable($data) ? $data() : $data;
        switch ($provider) {
            case "menu":
                app("addMenu")($data);
                break;
            default:
        }
        if (function_exists($func = "add".ucfirst($provider))) {
            $func($data);
        }
    }

    return $plugins;
}
