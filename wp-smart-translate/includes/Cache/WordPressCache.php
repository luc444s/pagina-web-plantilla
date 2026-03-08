<?php

declare(strict_types=1);

namespace WpSmartTranslate\Cache;

use WpSmartTranslate\Core\Config;

final class WordPressCache implements CacheInterface
{
    private Config $config;
    private CacheKeyGenerator $keyGenerator;

    public function __construct(Config $config, ?CacheKeyGenerator $keyGenerator = null)
    {
        $this->config = $config;
        $this->keyGenerator = $keyGenerator ?: new CacheKeyGenerator();
    }

    public function get(string $text, string $source, string $target): ?string
    {
        $key = 'wst_' . $this->keyGenerator->generate($text, $source, $target);
        $value = get_transient($key);

        return is_string($value) ? $value : null;
    }

    public function set(string $text, string $source, string $target, string $translation): bool
    {
        $key = 'wst_' . $this->keyGenerator->generate($text, $source, $target);
        $expiration = (int) $this->config->get('cache.expiration', MONTH_IN_SECONDS);

        return set_transient($key, $translation, $expiration);
    }
}
