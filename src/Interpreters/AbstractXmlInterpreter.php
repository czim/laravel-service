<?php
namespace Czim\Service\Interpreters;

use SimpleXMLElement;

abstract class AbstractXmlInterpreter extends AbstractInterpreter
{

    /**
     * Converts SimpleXml structure to array
     *
     * @param SimpleXmlElement|array|object $xml
     * @return array
     */
    protected function convertXmlObjectToArray($xml)
    {
        $array = (array) $xml;

        if (count($array) == 0) {
            $array = [ (string) $xml ];
        }

        if (is_array($array)) {

            foreach ($array as $key => $value) {

                if (is_object($value)) {

                    if (strpos(get_class($value), "SimpleXML") !== false) {
                        $array[ $key ] = $this->convertXmlObjectToArray($value);
                    } else {
                        $array[ $key ] = (array) $value;
                    }

                } elseif (is_array($value)) {

                    $array[ $key ] = $this->convertXmlObjectToArray($value);
                }
            }
        }

        return $array;
    }

    /**
     * Converts SimpleXml structure to array
     * using the clunky json encode/decode method
     *
     * @param SimpleXmlElement|array|object $xml
     * @return array|mixed
     */
    protected function convertXmlObjectToArrayViaJson($xml)
    {
        return json_decode(json_encode($xml), true);
    }

}
