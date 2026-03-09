<?php

declare(strict_types=1);

namespace WpSmartTranslate\DOM;

use DOMDocument;
use DOMNode;

final class TextExtractor
{
    private VisibleTextFilter $filter;
    private NodeMapper $mapper;

    /** @var string[] */
    private array $allowedTags;

    /**
     * @param string[] $allowedTags
     */
    public function __construct(VisibleTextFilter $filter, NodeMapper $mapper, array $allowedTags)
    {
        $this->filter = $filter;
        $this->mapper = $mapper;
        $this->allowedTags = $allowedTags;
    }

    /**
     * @return array<string,array<int,DOMNode>>
     */
    public function extract(DOMDocument $dom): array
    {
        $map = [];
        $xpath = new \DOMXPath($dom);

        $query = implode(' | ', array_map(static fn(string $tag): string => sprintf('//%s//text()', $tag), $this->allowedTags));

        if ($query === '') {
            return $map;
        }

        $nodes = $xpath->query($query);
        if (! $nodes) {
            return $map;
        }

        foreach ($nodes as $node) {
            if (! $node instanceof DOMNode || ! $this->filter->isAllowedTextNode($node)) {
                continue;
            }

            $text = trim((string) $node->nodeValue);
            $this->mapper->addTextNode($map, $text, $node);
        }

        return $map;
    }
}
