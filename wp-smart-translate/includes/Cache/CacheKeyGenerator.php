<?php

declare(strict_types=1);

namespace WpSmartTranslate\Cache;

final class CacheKeyGenerator
{
    public function generate(string $text, string $source, string $target): string
    {
        return hash('sha256', $source . '|' . $target . '|' . $text);
    }
}
