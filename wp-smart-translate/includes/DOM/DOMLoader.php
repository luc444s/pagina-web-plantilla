<?php

declare(strict_types=1);

namespace WpSmartTranslate\DOM;

use DOMDocument;

final class DOMLoader
{
    public function load(string $html): ?DOMDocument
    {
        if (! class_exists('DOMDocument')) {
            return null;
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $internalErrors = libxml_use_internal_errors(true);

        $wrapped = '<?xml encoding="utf-8" ?>' . $html;
        $loaded = $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

        return $loaded ? $dom : null;
    }

    public function save(DOMDocument $dom): string
    {
        $html = $dom->saveHTML();
        if (! is_string($html)) {
            return '';
        }

        return preg_replace('/^<\?xml.+?\?>/i', '', $html) ?: '';
    }
}
