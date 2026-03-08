<?php

declare(strict_types=1);

namespace WpSmartTranslate\Core;

final class Bootstrap
{
    public static function init(): void
    {
        spl_autoload_register([self::class, 'autoload']);

        add_action('plugins_loaded', static function () {
            $plugin = new Plugin();
            $plugin->boot();
        });
    }

    public static function autoload(string $class): void
    {
        $prefix = 'WpSmartTranslate\\';

        if (strpos($class, $prefix) !== 0) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $path = WP_SMART_TRANSLATE_PATH . 'includes/' . str_replace('\\', '/', $relative) . '.php';

        if (is_readable($path)) {
            require_once $path;
        }
    }
}
