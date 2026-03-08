<?php

declare(strict_types=1);

namespace WpSmartTranslate\Core;

use WpSmartTranslate\Support\Exceptions\ConfigurationException;

final class Config
{
    /** @var array<string,mixed> */
    private array $config;

    public function __construct(string $configFile)
    {
        if (! is_readable($configFile)) {
            throw new ConfigurationException('No se pudo cargar la configuración del plugin.');
        }

        $loaded = include $configFile;

        if (! is_array($loaded)) {
            throw new ConfigurationException('Formato de configuración inválido.');
        }

        $this->config = $this->sanitize($loaded);
    }

    /**
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $segments = explode('.', $key);
        $value = $this->config;

        foreach ($segments as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * @param array<string,mixed> $config
     * @return array<string,mixed>
     */
    private function sanitize(array $config): array
    {
        $config['source_language'] = sanitize_key((string) ($config['source_language'] ?? 'es'));
        $config['language_cookie'] = sanitize_key((string) ($config['language_cookie'] ?? 'wst_lang'));
        $config['enabled_languages'] = array_values(array_filter(array_map('sanitize_key', (array) ($config['enabled_languages'] ?? []))));

        $api = (array) ($config['api'] ?? []);
        $api['base_url'] = esc_url_raw((string) ($api['base_url'] ?? ''));
        $api['api_key'] = sanitize_text_field((string) ($api['api_key'] ?? ''));
        $api['timeout'] = max(1, absint($api['timeout'] ?? 8));
        $api['max_retries'] = max(0, absint($api['max_retries'] ?? 1));
        $config['api'] = $api;

        $config['translate_attributes'] = (bool) ($config['translate_attributes'] ?? false);
        $config['debug'] = (bool) ($config['debug'] ?? false);
        $config['excluded_paths'] = array_map('strval', (array) ($config['excluded_paths'] ?? []));
        $config['translatable_tags'] = array_values(array_filter(array_map('sanitize_key', (array) ($config['translatable_tags'] ?? []))));
        $config['translatable_attributes'] = array_values(array_filter(array_map('sanitize_key', (array) ($config['translatable_attributes'] ?? []))));

        $cache = (array) ($config['cache'] ?? []);
        $cache['driver'] = sanitize_key((string) ($cache['driver'] ?? 'database'));
        $cache['expiration'] = max(HOUR_IN_SECONDS, absint($cache['expiration'] ?? DAY_IN_SECONDS));
        $config['cache'] = $cache;

        return $config;
    }
}
