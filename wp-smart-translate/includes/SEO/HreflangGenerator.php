<?php

declare(strict_types=1);

namespace WpSmartTranslate\SEO;

use WpSmartTranslate\Core\Config;
use WpSmartTranslate\Language\LanguageContext;

final class HreflangGenerator
{
    private Config $config;
    private UrlLanguageRouter $router;

    public function __construct(Config $config, UrlLanguageRouter $router)
    {
        $this->config = $config;
        $this->router = $router;
    }

    public function render(LanguageContext $context): string
    {
        $enabled = (array) $this->config->get('enabled_languages', []);

        if (empty($enabled)) {
            return '';
        }

        $output = [];
        foreach ($enabled as $lang) {
            $url = esc_url($this->router->buildLanguageUrl((string) $lang));
            $output[] = sprintf('<link rel="alternate" hreflang="%s" href="%s" />', esc_attr((string) $lang), $url);
        }

        $defaultUrl = esc_url($this->router->buildLanguageUrl($context->getSourceLanguage()));
        $output[] = sprintf('<link rel="alternate" hreflang="x-default" href="%s" />', $defaultUrl);

        return implode("\n", $output) . "\n";
    }
}
