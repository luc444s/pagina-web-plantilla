<?php

declare(strict_types=1);

namespace WpSmartTranslate\Core;

use WpSmartTranslate\Cache\CacheInterface;
use WpSmartTranslate\Cache\DatabaseCache;
use WpSmartTranslate\Cache\WordPressCache;
use WpSmartTranslate\Capture\OutputBuffer;
use WpSmartTranslate\Capture\RequestGuard;
use WpSmartTranslate\DOM\AttributeExtractor;
use WpSmartTranslate\DOM\DOMLoader;
use WpSmartTranslate\DOM\DOMReplacer;
use WpSmartTranslate\DOM\NodeMapper;
use WpSmartTranslate\DOM\TextExtractor;
use WpSmartTranslate\DOM\VisibleTextFilter;
use WpSmartTranslate\Language\LanguageDetector;
use WpSmartTranslate\SEO\HreflangGenerator;
use WpSmartTranslate\SEO\UrlLanguageRouter;
use WpSmartTranslate\Support\Logger;
use WpSmartTranslate\Translation\LibreTranslateClient;
use WpSmartTranslate\Translation\TranslationService;

final class Plugin
{
    private Config $config;

    public function boot(): void
    {
        $this->config = new Config(WP_SMART_TRANSLATE_PATH . 'config/plugin-config.php');

        add_action('template_redirect', [$this, 'startCapture'], 1);
        add_action('wp_head', [$this, 'outputHreflangTags'], 1);
    }

    public function startCapture(): void
    {
        $logger = new Logger((bool) $this->config->get('debug', false));
        $router = new Router($this->config);

        $guard = new RequestGuard($router);
        if (! $guard->shouldProcess()) {
            return;
        }

        $languageDetector = new LanguageDetector($this->config);
        $languageContext = $languageDetector->detect();

        if (! $languageContext->requiresTranslation()) {
            return;
        }

        $cache = $this->buildCache();

        $domLoader = new DOMLoader();
        $filter = new VisibleTextFilter($this->config);
        $nodeMapper = new NodeMapper();
        $textExtractor = new TextExtractor($filter, $nodeMapper, (array) $this->config->get('translatable_tags', []));
        $attributeExtractor = new AttributeExtractor(
            $filter,
            $nodeMapper,
            (array) $this->config->get('translatable_attributes', []),
            (bool) $this->config->get('translate_attributes', false)
        );
        $replacer = new DOMReplacer();

        $apiClient = new LibreTranslateClient($this->config, $logger);
        $translator = new TranslationService($cache, $apiClient, $logger);

        $buffer = new OutputBuffer(
            $domLoader,
            $textExtractor,
            $attributeExtractor,
            $replacer,
            $translator,
            $languageContext,
            $logger
        );

        $buffer->start();
    }

    public function outputHreflangTags(): void
    {
        if (is_admin()) {
            return;
        }

        $languageDetector = new LanguageDetector($this->config);
        $context = $languageDetector->detect();
        $seoRouter = new UrlLanguageRouter($this->config);
        $generator = new HreflangGenerator($this->config, $seoRouter);

        echo $generator->render($context); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    private function buildCache(): CacheInterface
    {
        $driver = (string) $this->config->get('cache.driver', 'database');

        if ($driver === 'transient') {
            return new WordPressCache($this->config);
        }

        return new DatabaseCache($this->config);
    }
}
