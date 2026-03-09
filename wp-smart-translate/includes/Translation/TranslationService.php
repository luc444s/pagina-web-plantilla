<?php

declare(strict_types=1);

namespace WpSmartTranslate\Translation;

use WpSmartTranslate\Cache\CacheInterface;
use WpSmartTranslate\Support\Logger;

final class TranslationService
{
    private CacheInterface $cache;
    private ApiClient $apiClient;
    private Logger $logger;

    public function __construct(CacheInterface $cache, ApiClient $apiClient, Logger $logger)
    {
        $this->cache = $cache;
        $this->apiClient = $apiClient;
        $this->logger = $logger;
    }

    /**
     * @param string[] $texts
     * @return array<string,string>
     */
    public function translateBatch(array $texts, string $source, string $target): array
    {
        $batch = new TranslationBatch($texts);
        $resolved = [];
        $missing = [];
        $cacheHits = 0;

        foreach ($batch->texts() as $text) {
            $cached = $this->cache->get($text, $source, $target);
            if ($cached !== null) {
                $resolved[$text] = $cached;
                $cacheHits++;
                continue;
            }

            $missing[] = $text;
        }

        if (! empty($missing)) {
            $translated = $this->apiClient->translate($missing, $source, $target);

            foreach ($translated as $original => $result) {
                if ($result === '') {
                    continue;
                }

                $resolved[$original] = $result;
                $this->cache->set($original, $source, $target, $result);
            }
        }

        $this->logger->debug(
            sprintf(
                'Traducción resuelta. Total:%d cache:%d api:%d',
                count($batch->texts()),
                $cacheHits,
                count($missing)
            )
        );

        return (new TranslationResult($resolved))->all();
    }
}
