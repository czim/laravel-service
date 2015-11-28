<?php
namespace Czim\Service\Interpreters\Xml;

use Czim\Service\Contracts\XmlObjectConverterInterface;
use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * For converting DOMDocument/Element objects to array
 * if they were created using a DomDocument conversion approach, after which
 * the namespaces may be stripped before the result array is returned.
 *
 * Only use this to deal with problematic XML that cannot be interpreted/parsed/converted normally.
 */
class DomObjectToArrayConverter implements XmlObjectConverterInterface
{

    /**
     * @param mixed|DomDocument|DomElement|DomNode $object
     * @return array
     */
    public function convert($object)
    {
        return $this->convertDomtoArray($object);
    }

    /**
     * Converts DomDocument to array with all attributes in it
     *
     * @param  DOMDocument|DOMElement|DomNode $root
     * @return array
     */
    function convertDomToArray($root)
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

                if ( ! isset($result[ $child->nodeName ])) {

                    $result[ $child->nodeName ] = $this->convertDomToArray($child);

                } else {

                    if ( ! isset($groups[ $child->nodeName ])) {

                        $result[ $child->nodeName ] = array($result[ $child->nodeName ]);
                        $groups[ $child->nodeName ] = 1;
                    }

                    $result[ $child->nodeName ][] = $this->convertDomToArray($child);
                }
            }
        }

        return $result;
    }

}
