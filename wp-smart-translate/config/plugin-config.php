<?php

declare(strict_types=1);

return [
    'source_language' => 'es',
    'enabled_languages' => ['es', 'en', 'fr', 'de', 'it', 'pt'],
    'language_cookie' => 'wst_lang',
    'api' => [
        'base_url' => 'https://tu-vps-traduccion.example.com',
        'api_key' => '',
        'timeout' => 8,
        'max_retries' => 1,
    ],
    'translate_attributes' => false,
    'debug' => false,
    'excluded_paths' => [
        '/wp-admin',
        '/wp-json',
        '/feed',
        '/robots.txt',
        '/sitemap',
        '/xmlrpc.php',
        '/cart',
        '/checkout',
        '/my-account',
    ],
    'translatable_tags' => [
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'p', 'span', 'button', 'a', 'label',
        'li', 'td', 'th', 'strong', 'em', 'b', 'i', 'small',
    ],
    'translatable_attributes' => ['placeholder', 'title', 'alt', 'aria-label'],
    'cache' => [
        'driver' => 'database',
        'expiration' => MONTH_IN_SECONDS * 12,
    ],
    'seo' => [
        'url_prefix_enabled' => true,
        'default_locale' => 'es_ES',
    ],
];
