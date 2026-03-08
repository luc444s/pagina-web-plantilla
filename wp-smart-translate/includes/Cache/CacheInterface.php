<?php

declare(strict_types=1);

namespace WpSmartTranslate\Cache;

interface CacheInterface
{
    public function get(string $text, string $source, string $target): ?string;

    public function set(string $text, string $source, string $target, string $translation): bool;
}
