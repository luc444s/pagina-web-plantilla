<?php

declare(strict_types=1);

namespace WpSmartTranslate\Support;

final class Helpers
{
    public static function startsWith(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) === 0;
    }
}
