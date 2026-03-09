<?php

declare(strict_types=1);

namespace WpSmartTranslate\Translation;

interface ApiClient
{
    /**
     * @param string[] $texts
     * @return array<string,string>
     */
    public function translate(array $texts, string $source, string $target): array;
}
