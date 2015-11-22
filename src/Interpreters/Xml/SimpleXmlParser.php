<?php
namespace Czim\Service\Interpreters\Xml;

use Czim\Service\Contracts\XmlParserInterface;
use Czim\Service\Exceptions\CouldNotInterpretXmlResponse;

class SimpleXmlParser implements XmlParserInterface
{
    // whether to remove CDATA tags to get all content normally
    const STRIP_CDATA_TAGS = true;


    /**
     * @param string $xml
     * @return mixed
     * @throws CouldNotInterpretXmlResponse
     */
    public function parse($xml)
    {
        try {

            if (self::STRIP_CDATA_TAGS) {

                return simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            }

            return simplexml_load_string($xml);

        } catch (\ErrorException $e) {

            $message = $e->getMessage();

            if (preg_match('#^\s*simplexml_load_string\(\):\s*(.*)$#i', $message, $matches)) {
                $message = $matches[1];
            }

            throw new CouldNotInterpretXmlResponse($message, $e->getCode(), $e);
        }
    }


    /**
     * In special cases where XML is malformed, attempt to salvage the
     * last element anyway.
     *
     * Not used at the moment..
     *
     * @param string $xml
     * @return array
     * @throws CouldNotInterpretXmlResponse
     */
    protected function getArrayForSpecialCase($xml)
    {
        $regEx            = '#\s*(&lt;\?xml.*interface&gt;)\s*#is';
        $regExReplace     = '#\s*>\s*(&lt;\?xml.*interface&gt;)\s*<[^>]+>\s*#is';
        $regExReplaceWith = '/>';

        // what can happen is that the valid XML element suddenly has,
        // as its element content, a full version of url-encoded XML.
        if ( ! preg_match($regEx, $xml)) {
            return [0 => $xml];
        }
        $xml = preg_replace($regExReplace, $regExReplaceWith, $xml);

        return $this->parse($xml);
    }

}
