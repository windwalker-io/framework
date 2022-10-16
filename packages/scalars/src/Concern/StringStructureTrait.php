<?php

/**
 * Part of framework project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Scalars\Concern;

use Windwalker\Data\Collection;

/**
 * Trait StringStructureTrait
 */
trait StringStructureTrait
{
    public function parseToCollection(string $format, array $options = []): Collection
    {
        if (!class_exists(Collection::class)) {
            throw new \DomainException('Please install windwalker/data first.');
        }

        return Collection::from($this->string, $format, $options);
    }

    public function jsonDecode(int $depth = 512, int $options = 0): Collection
    {
        if (!class_exists(Collection::class)) {
            throw new \DomainException('Please install windwalker/data first.');
        }

        return Collection::from($this->string, 'json', compact('depth', 'options'));
    }

    public function toDOMDocument(): \DOMDocument
    {
        $impl = new \DOMImplementation();
        $dom = $impl->createDocument();
        $dom->encoding = 'UTF-8';

        $dom->loadXML($this->string);

        return $dom;
    }

    public function toHTMLDocument(): \DOMDocument
    {
        $impl = new \DOMImplementation();
        $dt = $impl->createDocumentType('html');

        $dom = $impl->createDocument('', '', $dt);
        $dom->encoding = 'UTF-8';

        $dom->loadHTML($this->string, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        return $dom;
    }

    public function toSimpleXML(): \SimpleXMLElement
    {
        return new \SimpleXMLElement($this->string);
    }
}
