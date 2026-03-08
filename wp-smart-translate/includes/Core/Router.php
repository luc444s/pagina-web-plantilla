<?php

declare(strict_types=1);

namespace WpSmartTranslate\Core;

use WpSmartTranslate\Language\LanguageContext;

final class Router
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function normalizeRequestPath(string $requestUri): string
    {
        $path = wp_parse_url($requestUri, PHP_URL_PATH);

        if (! is_string($path)) {
            return '/';
        }

        return '/' . ltrim($path, '/');
    }

    public function stripLanguagePrefix(string $path, LanguageContext $context): string
    {
        $lang = $context->getTargetLanguage();

        if (! $lang) {
            return $path;
        }

        $prefix = '/' . $lang . '/';

        if (strpos($path . '/', $prefix) === 0) {
            $trimmed = '/' . ltrim(substr($path, strlen($prefix) - 1), '/');
            return $trimmed === '//' ? '/' : $trimmed;
        }

        return $path;
    }

    public function isPathExcluded(string $path): bool
    {
        $excluded = (array) $this->config->get('excluded_paths', []);

        foreach ($excluded as $excludedPath) {
            $excludedPath = '/' . ltrim((string) $excludedPath, '/');

            if (strpos($path, $excludedPath) === 0) {
                return true;
            }
        }

        return false;
    }
}
