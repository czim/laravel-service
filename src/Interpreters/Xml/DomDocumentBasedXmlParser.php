<?php

declare(strict_types=1);

namespace Czim\Service\Interpreters\Xml;

use Czim\Service\Contracts\XmlParserInterface;
use DOMDocument;
use DOMElement;
use DOMNode;
use SimpleXMLElement;

/**
 * Parses XML to array through a DOM import approach.
 * Returns DOMElement, not a SimpleXml element!
 *
 * Convert to array using DomObjectToArrayConverter.
 */
class DomDocumentBasedXmlParser implements XmlParserInterface
{
    /**
     * Create fixed, clean XML object (array) from the response XML string.
     * This strips namespaces from the XML through a bit of a hack.
     *
     * @param string $xml
     * @return DOMNode|null
     */
    public function parse(string $xml): ?DOMNode
    {
        $dom = $this->buildDomDocumentFromXml($xml);

        return $dom->childNodes->item(0);
    }


    protected function buildDomDocumentFromXml(string $xml): DOMDocument
    {
        // See the following URL for this trick:
        // http://stackoverflow.com/questions/15223224/how-to-remove-all-namespaces-from-xml-in-php-tags-and-attributes

        $sxe     = new SimpleXMLElement($xml);
        $dom_sxe = dom_import_simplexml($sxe);

        $dom     = new DOMDocument('1.0');
        $dom_sxe = $dom->importNode($dom_sxe, true);
        $dom->appendChild($dom_sxe);

        /** @var DOMElement $element */
        $element = $dom->childNodes->item(0);

        foreach ($sxe->getDocNamespaces(true) as $name => $uri) {
            $element->removeAttributeNS($uri, $name);
        }

        return $dom;
    }
}
