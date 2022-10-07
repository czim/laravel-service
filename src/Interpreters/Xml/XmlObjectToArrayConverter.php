<?php

declare(strict_types=1);

namespace Czim\Service\Interpreters\Xml;

use Czim\Service\Contracts\XmlObjectConverterInterface;
use SimpleXMLElement;

/**
 * For converting SimpleXml objects to array.
 */
class XmlObjectToArrayConverter implements XmlObjectConverterInterface
{
    /**
     * @param object $object
     * @return array<int|string, mixed>
     */
    public function convert(object $object): array
    {
        return $this->convertXmlObjectToArray($object);
    }

    /**
     * Converts SimpleXml structure to array
     *
     * @param SimpleXmlElement|array<int|string, mixed>|object $xml
     * @return array
     */
    protected function convertXmlObjectToArray(mixed $xml): array
    {
        $array = (array) $xml;

        if (count($array) == 0) {
            $array = [ (string) $xml ];
        }


        foreach ($array as $key => $value) {
            if (is_object($value)) {
                if (str_contains(get_class($value), 'SimpleXML')) {
                    $array[ $key ] = $this->convertXmlObjectToArray($value);
                } else {
                    $array[ $key ] = $this->convertXmlObjectToArray((array) $value);
                }
            } elseif (is_array($value)) {
                $array[ $key ] = $this->convertXmlObjectToArray($value);
            }
        }

        return $array;
    }
}
