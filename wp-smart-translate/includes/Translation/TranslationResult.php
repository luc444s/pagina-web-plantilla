<?php

declare(strict_types=1);

namespace WpSmartTranslate\Translation;

final class TranslationResult
{
    /** @var array<string,string> */
    private array $translations;

    /**
     * @param array<string,string> $translations
     */
    public function __construct(array $translations)
    {
        $this->translations = $translations;
    }

    /**
     * @return array<string,string>
     */
    public function all(): array
    {
        return $this->translations;
    }
}
