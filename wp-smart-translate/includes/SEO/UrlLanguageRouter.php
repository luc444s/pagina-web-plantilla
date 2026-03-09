<?php

declare(strict_types=1);

namespace WpSmartTranslate\SEO;

use WpSmartTranslate\Core\Config;

final class UrlLanguageRouter
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function buildLanguageUrl(string $lang): string
    {
        $home = home_url('/');
        $request = isset($_SERVER['REQUEST_URI']) ? wp_unslash((string) $_SERVER['REQUEST_URI']) : '/';
        $path = (string) wp_parse_url($request, PHP_URL_PATH);
        $segments = array_values(array_filter(explode('/', trim((string) $path, '/'))));
        $enabled = (array) $this->config->get('enabled_languages', []);

        if (! empty($segments) && in_array($segments[0], $enabled, true)) {
            array_shift($segments);
        }

        array_unshift($segments, $lang);

        return trailingslashit($home) . implode('/', $segments) . '/';
    }
}
