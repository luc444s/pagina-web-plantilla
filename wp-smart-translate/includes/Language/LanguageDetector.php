<?php

declare(strict_types=1);

namespace WpSmartTranslate\Language;

use WpSmartTranslate\Core\Config;

final class LanguageDetector
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function detect(): LanguageContext
    {
        $source = (string) $this->config->get('source_language', 'es');
        $enabled = (array) $this->config->get('enabled_languages', [$source]);

        $detected = $this->detectFromUrl($enabled)
            ?? $this->detectFromCookie($enabled)
            ?? $this->detectFromHeader($enabled)
            ?? $source;

        if (! in_array($detected, $enabled, true)) {
            $detected = $source;
        }

        return new LanguageContext($source, $detected);
    }

    /**
     * @param string[] $enabled
     */
    private function detectFromUrl(array $enabled): ?string
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash((string) $_SERVER['REQUEST_URI']) : '';
        $path = (string) wp_parse_url($uri, PHP_URL_PATH);
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        if (empty($segments)) {
            return null;
        }

        $candidate = sanitize_key((string) $segments[0]);

        return in_array($candidate, $enabled, true) ? $candidate : null;
    }

    /**
     * @param string[] $enabled
     */
    private function detectFromCookie(array $enabled): ?string
    {
        $cookieName = (string) $this->config->get('language_cookie', 'wst_lang');
        $candidate = isset($_COOKIE[$cookieName]) ? sanitize_key(wp_unslash((string) $_COOKIE[$cookieName])) : '';

        return in_array($candidate, $enabled, true) ? $candidate : null;
    }

    /**
     * @param string[] $enabled
     */
    private function detectFromHeader(array $enabled): ?string
    {
        $header = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? sanitize_text_field(wp_unslash((string) $_SERVER['HTTP_ACCEPT_LANGUAGE'])) : '';

        if ($header === '') {
            return null;
        }

        $parts = explode(',', strtolower($header));

        foreach ($parts as $part) {
            $lang = sanitize_key(substr(trim($part), 0, 2));
            if (in_array($lang, $enabled, true)) {
                return $lang;
            }
        }

        return null;
    }
}
