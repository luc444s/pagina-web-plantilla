<?php

declare(strict_types=1);

namespace WpSmartTranslate\DOM;

use DOMNode;
use WpSmartTranslate\Core\Config;

final class VisibleTextFilter
{
    /** @var string[] */
    private array $excludedAncestors = [
        'script', 'style', 'noscript', 'textarea', 'code', 'pre',
        'svg', 'canvas', 'iframe', 'template',
    ];

    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function isAllowedTextNode(DOMNode $node): bool
    {
        if ($node->nodeType !== XML_TEXT_NODE) {
            return false;
        }

        $value = trim((string) $node->nodeValue);
        if ($value === '') {
            return false;
        }

        $parent = $node->parentNode;
        if (! $parent || $parent->nodeType !== XML_ELEMENT_NODE) {
            return false;
        }

        $tag = strtolower($parent->nodeName);
        $allowedTags = (array) $this->config->get('translatable_tags', []);

        if (! in_array($tag, $allowedTags, true)) {
            return false;
        }

        return ! $this->hasExcludedAncestor($node);
    }

    public function isAllowedAttribute(string $attributeName, DOMNode $node): bool
    {
        if ($this->hasExcludedAncestor($node)) {
            return false;
        }

        $allowedAttributes = (array) $this->config->get('translatable_attributes', []);

        return in_array(strtolower($attributeName), $allowedAttributes, true);
    }

    private function hasExcludedAncestor(DOMNode $node): bool
    {
        $current = $node->parentNode;

        while ($current instanceof DOMNode) {
            if ($current->nodeType === XML_ELEMENT_NODE) {
                $tag = strtolower($current->nodeName);

                if (in_array($tag, $this->excludedAncestors, true)) {
                    return true;
                }

                if ($current->attributes !== null) {
                    if ($current->attributes->getNamedItem('data-no-translate')) {
                        return true;
                    }

                    $class = $current->attributes->getNamedItem('class');
                    if ($class && strpos(strtolower($class->nodeValue), 'notranslate') !== false) {
                        return true;
                    }
                }
            }

            $current = $current->parentNode;
        }

        return false;
    }
}
