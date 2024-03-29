<?php

declare(strict_types=1);

namespace Czim\Service\Interpreters\Xml;

use Czim\Service\Contracts\XmlObjectConverterInterface;
use DOMNode;

/**
 * For converting DOMDocument/Element objects to array.
 * If they were created using a DomDocument conversion approach, after which
 * the namespaces may be stripped before the result array is returned.
 *
 * Only use this to deal with problematic XML that cannot be interpreted/parsed/converted normally.
 */
class DomObjectToArrayConverter implements XmlObjectConverterInterface
{
    /**
     * @param DOMNode $object
     * @return array<int|string, mixed>
     */
    public function convert(object $object): array
    {
        return $this->convertDomtoArray($object);
    }

    /**
     * Converts DomDocument to array with all attributes in it
     *
     * @param DomNode $root
     * @return array<int|string, mixed>|string
     */
    protected function convertDomToArray(DOMNode $root): array|string
    {
        $result = [];

        if ($root->hasAttributes()) {
            $attrs = $root->attributes;

            foreach ($attrs as $attr) {
                $result['@attributes'][ $attr->name ] = $attr->value;
            }
        }

        if ($root->hasChildNodes()) {
            $children = $root->childNodes;

            if ($children->length == 1) {
                $child = $children->item(0);

                if ($child->nodeType == XML_TEXT_NODE) {
                    $result['_value'] = $child->nodeValue;

                    return (count($result) == 1)
                        ? $result['_value']
                        : $result;
                }
            }

            $groups = [];

            foreach ($children as $child) {
                if (! isset($result[ $child->nodeName ])) {
                    $result[ $child->nodeName ] = $this->convertDomToArray($child);
                } else {
                    if (! isset($groups[ $child->nodeName ])) {
                        $result[ $child->nodeName ] = [$result[ $child->nodeName ]];
                        $groups[ $child->nodeName ] = 1;
                    }

                    $result[ $child->nodeName ][] = $this->convertDomToArray($child);
                }
            }
        }

        return $result;
    }
}
