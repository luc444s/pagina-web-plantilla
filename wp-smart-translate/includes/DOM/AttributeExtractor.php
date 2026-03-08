<?php

declare(strict_types=1);

namespace WpSmartTranslate\DOM;

use DOMAttr;
use DOMDocument;
use DOMElement;

final class AttributeExtractor
{
    private VisibleTextFilter $filter;
    private NodeMapper $mapper;

    /** @var string[] */
    private array $allowedAttributes;

    private bool $enabled;

    /**
     * @param string[] $allowedAttributes
     */
    public function __construct(VisibleTextFilter $filter, NodeMapper $mapper, array $allowedAttributes, bool $enabled)
    {
        $this->filter = $filter;
        $this->mapper = $mapper;
        $this->allowedAttributes = $allowedAttributes;
        $this->enabled = $enabled;
    }

    /**
     * @return array<string,array<int,DOMAttr>>
     */
    public function extract(DOMDocument $dom): array
    {
        $map = [];

        if (! $this->enabled) {
            return $map;
        }

        $nodes = $dom->getElementsByTagName('*');

        foreach ($nodes as $node) {
            if (! $node instanceof DOMElement || ! $node->hasAttributes()) {
                continue;
            }

            foreach ($this->allowedAttributes as $attributeName) {
                if (! $node->hasAttribute($attributeName)) {
                    continue;
                }

                $attribute = $node->getAttributeNode($attributeName);
                if (! $attribute instanceof DOMAttr) {
                    continue;
                }

                $value = trim($attribute->value);
                if ($value === '') {
                    continue;
                }

                if (! $this->filter->isAllowedAttribute($attributeName, $node)) {
                    continue;
                }

                $this->mapper->addAttributeNode($map, $value, $attribute);
            }
        }

        return $map;
    }
}
