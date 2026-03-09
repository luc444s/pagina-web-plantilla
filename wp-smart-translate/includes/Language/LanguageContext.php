<?php

declare(strict_types=1);

namespace WpSmartTranslate\Language;

final class LanguageContext
{
    private string $sourceLanguage;
    private string $targetLanguage;

    public function __construct(string $sourceLanguage, string $targetLanguage)
    {
        $this->sourceLanguage = $sourceLanguage;
        $this->targetLanguage = $targetLanguage;
    }

    public function getSourceLanguage(): string
    {
        return $this->sourceLanguage;
    }

    public function getTargetLanguage(): string
    {
        return $this->targetLanguage;
    }

    public function requiresTranslation(): bool
    {
        return $this->sourceLanguage !== $this->targetLanguage;
    }
}
