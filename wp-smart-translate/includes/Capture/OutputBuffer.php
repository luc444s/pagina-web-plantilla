<?php

declare(strict_types=1);

namespace WpSmartTranslate\Capture;

use WpSmartTranslate\DOM\AttributeExtractor;
use WpSmartTranslate\DOM\DOMLoader;
use WpSmartTranslate\DOM\DOMReplacer;
use WpSmartTranslate\DOM\TextExtractor;
use WpSmartTranslate\Language\LanguageContext;
use WpSmartTranslate\Support\Logger;
use WpSmartTranslate\Translation\TranslationService;

final class OutputBuffer
{
    private DOMLoader $domLoader;
    private TextExtractor $textExtractor;
    private AttributeExtractor $attributeExtractor;
    private DOMReplacer $replacer;
    private TranslationService $translationService;
    private LanguageContext $languageContext;
    private Logger $logger;

    public function __construct(
        DOMLoader $domLoader,
        TextExtractor $textExtractor,
        AttributeExtractor $attributeExtractor,
        DOMReplacer $replacer,
        TranslationService $translationService,
        LanguageContext $languageContext,
        Logger $logger
    ) {
        $this->domLoader = $domLoader;
        $this->textExtractor = $textExtractor;
        $this->attributeExtractor = $attributeExtractor;
        $this->replacer = $replacer;
        $this->translationService = $translationService;
        $this->languageContext = $languageContext;
        $this->logger = $logger;
    }

    public function start(): void
    {
        ob_start([$this, 'process']);
    }

    public function process(string $html): string
    {
        if (trim($html) === '' || stripos($html, '<html') === false) {
            return $html;
        }

        $dom = $this->domLoader->load($html);
        if (! $dom) {
            return $html;
        }

        $textMap = $this->textExtractor->extract($dom);
        $attributeMap = $this->attributeExtractor->extract($dom);

        $catalog = array_merge(array_keys($textMap), array_keys($attributeMap));
        if (empty($catalog)) {
            return $html;
        }

        $translations = $this->translationService->translateBatch(
            $catalog,
            $this->languageContext->getSourceLanguage(),
            $this->languageContext->getTargetLanguage()
        );

        $this->replacer->replaceTextNodes($textMap, $translations);
        $this->replacer->replaceAttributeNodes($attributeMap, $translations);

        $translatedHtml = $this->domLoader->save($dom);
        if ($translatedHtml === '') {
            $this->logger->warning('No se pudo reconstruir el HTML traducido, devolviendo HTML original.');
            return $html;
        }

        return $translatedHtml;
    }
}
