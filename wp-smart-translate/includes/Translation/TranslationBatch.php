<?php

declare(strict_types=1);

namespace WpSmartTranslate\Translation;

final class TranslationBatch
{
    /** @var string[] */
    private array $texts;

    /**
     * @param string[] $texts
     */
    public function __construct(array $texts)
    {
        $this->texts = array_values(array_unique(array_filter(array_map('trim', $texts))));
    }

    /**
     * @return string[]
     */
    public function texts(): array
    {
        return $this->texts;
    }
}
