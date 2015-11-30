<?php
namespace Czim\Service\Interpreters\Xml;

use Czim\Service\Contracts\XmlObjectConverterInterface;
use SimpleXMLElement;

/**
 * For converting SimpleXml objects to array
 */
class XmlObjectToArrayConverter implements XmlObjectConverterInterface
{

    /**
     * @param mixed $object
     * @return array
     */
    public function convert($object)
    {
        return $this->convertXmlObjectToArray($object);
    }

    /**
     * Converts SimpleXml structure to array
     *
     * @param SimpleXmlElement|array|object $xml
     * @return array
     */
    protected function convertXmlObjectToArray($xml)
    {
        $array = (array) $xml;

        if (count($array) == 0 && ! is_array($xml)) {

            $array = [ (string) $xml ];
        }

        if (is_array($array)) {

            foreach ($array as $key => $value) {

                if (is_object($value)) {

                    if (strpos(get_class($value), "SimpleXML") !== false) {

                        $array[ $key ] = $this->convertXmlObjectToArray($value);

                    } else {

                        $array[ $key ] = $this->convertXmlObjectToArray( (array) $value );
                    }

                } elseif (is_array($value)) {

                    $array[ $key ] = $this->convertXmlObjectToArray($value);
                }
            }
        }

        return $array;
    }

}
