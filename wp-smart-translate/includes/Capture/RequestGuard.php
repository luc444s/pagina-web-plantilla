<?php

declare(strict_types=1);

namespace WpSmartTranslate\Capture;

use WpSmartTranslate\Core\Router;

final class RequestGuard
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function shouldProcess(): bool
    {
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return false;
        }

        if ((defined('REST_REQUEST') && REST_REQUEST) || is_feed() || is_robots() || is_trackback()) {
            return false;
        }

        $uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash((string) $_SERVER['REQUEST_URI']) : '/';
        $path = $this->router->normalizeRequestPath($uri);

        if ($this->router->isPathExcluded($path)) {
            return false;
        }

        if ($this->isXmlOrJsonContext()) {
            return false;
        }

        return ! headers_sent();
    }

    private function isXmlOrJsonContext(): bool
    {
        $contentType = function_exists('wp_is_json_request') && wp_is_json_request();

        if ($contentType) {
            return true;
        }

        $uri = isset($_SERVER['REQUEST_URI']) ? strtolower((string) wp_unslash($_SERVER['REQUEST_URI'])) : '';

        return (strpos($uri, '.xml') !== false || strpos($uri, '.json') !== false);
    }
}
