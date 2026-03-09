<?php

declare(strict_types=1);

namespace WpSmartTranslate\Translation;

use WpSmartTranslate\Core\Config;
use WpSmartTranslate\Support\Logger;

final class LibreTranslateClient implements ApiClient
{
    private Config $config;
    private Logger $logger;

    public function __construct(Config $config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param string[] $texts
     * @return array<string,string>
     */
    public function translate(array $texts, string $source, string $target): array
    {
        $texts = array_values(array_unique(array_filter(array_map('trim', $texts))));
        if (empty($texts)) {
            return [];
        }

        $endpoint = trailingslashit((string) $this->config->get('api.base_url')) . 'translate';
        $apiKey = (string) $this->config->get('api.api_key', '');
        $timeout = (int) $this->config->get('api.timeout', 8);
        $maxRetries = (int) $this->config->get('api.max_retries', 1);

        // Contrato nativo de LibreTranslate.
        $payload = [
            'q' => $texts,
            'source' => $source,
            'target' => $target,
            'format' => 'text',
        ];

        $headers = ['Content-Type' => 'application/json'];
        if ($apiKey !== '') {
            $headers['X-API-Key'] = $apiKey;
        }

        for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
            $response = wp_remote_post(
                $endpoint,
                [
                    'method' => 'POST',
                    'headers' => $headers,
                    'timeout' => $timeout,
                    'body' => wp_json_encode($payload),
                ]
            );

            if (is_wp_error($response)) {
                $this->logger->warning('Fallo de red al traducir: ' . $response->get_error_message());
                continue;
            }

            $statusCode = (int) wp_remote_retrieve_response_code($response);
            $rawBody = wp_remote_retrieve_body($response);

            if ($statusCode < 200 || $statusCode > 299) {
                $this->logger->warning(sprintf('Respuesta HTTP inválida %d del servidor de traducción.', $statusCode));
                continue;
            }

            $body = json_decode((string) $rawBody, true);
            if (! is_array($body) || ! isset($body['translatedText']) || ! is_array($body['translatedText'])) {
                $this->logger->warning('Respuesta de LibreTranslate inválida: falta translatedText como array.');
                continue;
            }

            $translatedTexts = array_values($body['translatedText']);
            if (count($translatedTexts) !== count($texts)) {
                $this->logger->warning(
                    sprintf(
                        'Cantidad de traducciones inconsistente. Enviadas:%d Recibidas:%d',
                        count($texts),
                        count($translatedTexts)
                    )
                );
                continue;
            }

            // Reconstruye el mapa esperado por el pipeline interno: [original => translated].
            $translations = [];
            foreach ($texts as $index => $originalText) {
                $translatedValue = $translatedTexts[$index] ?? '';

                if (! is_string($translatedValue) || $translatedValue === '') {
                    continue;
                }

                $translations[$originalText] = $translatedValue;
            }

            return $translations;
        }

        return [];
    }
}
