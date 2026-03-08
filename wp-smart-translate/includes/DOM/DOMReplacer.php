<?php

declare(strict_types=1);

namespace WpSmartTranslate\DOM;

use DOMAttr;
use DOMNode;

final class DOMReplacer
{
    /**
     * @param array<string,array<int,DOMNode>> $map
     * @param array<string,string> $translations
     */
    public function replaceTextNodes(array $map, array $translations): void
    {
        foreach ($map as $source => $nodes) {
            if (! isset($translations[$source])) {
                continue;
            }

            foreach ($nodes as $node) {
                if ($node instanceof DOMNode) {
                    $node->nodeValue = $translations[$source];
                }
            }
        }
    }

    /**
     * @param array<string,array<int,DOMAttr>> $map
     * @param array<string,string> $translations
     */
    public function replaceAttributeNodes(array $map, array $translations): void
    {
        foreach ($map as $source => $attributes) {
            if (! isset($translations[$source])) {
                continue;
            }

            foreach ($attributes as $attribute) {
                if ($attribute instanceof DOMAttr) {
                    $attribute->value = $translations[$source];
                }
            }
        }
    }
}
