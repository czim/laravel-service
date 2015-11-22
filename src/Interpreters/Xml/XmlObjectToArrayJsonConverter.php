<?php
namespace Czim\Service\Interpreters\Xml;

use Czim\Service\Contracts\XmlObjectConverterInterface;

/**
 * For converting SimpleXml objects to array through the json-encode/decode trick
 *
 * Not used by default; use this for debugging purposes only, since it is
 * very inefficient and uses a LOT of memory.
 */
class XmlObjectToArrayJsonConverter implements XmlObjectConverterInterface
{

    /**
     * @param mixed $object
     * @return array
     */
    public function convert($object)
    {
        return json_decode(json_encode($object), true);
    }

}
