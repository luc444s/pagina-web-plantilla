<?php

declare(strict_types=1);

namespace WpSmartTranslate\DOM;

use DOMAttr;
use DOMNode;

final class NodeMapper
{
    /**
     * @param array<string,array<int,DOMNode>> $map
     */
    public function addTextNode(array &$map, string $text, DOMNode $node): void
    {
        if (! isset($map[$text])) {
            $map[$text] = [];
        }

        $map[$text][] = $node;
    }

    /**
     * @param array<string,array<int,DOMAttr>> $map
     */
    public function addAttributeNode(array &$map, string $text, DOMAttr $attribute): void
    {
        if (! isset($map[$text])) {
            $map[$text] = [];
        }

        $map[$text][] = $attribute;
    }
}
