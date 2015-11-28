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
        // note that this resets the PHP error handler -- if anything goes wrong,
        // look here first
        libxml_use_internal_errors(true);

        try {

            $parsed = (self::STRIP_CDATA_TAGS)
                    ?   simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)
                    :   $parsed = simplexml_load_string($xml);

            if ( ! $parsed) {

                throw new CouldNotInterpretXmlResponse( $this->getLibXmlErrorMessage() );
            }

            libxml_clear_errors();

            return $parsed;

        } catch (\ErrorException $e) {

            $message = $e->getMessage();

            if (preg_match('#^\s*simplexml_load_string\(\):\s*(.*)$#i', $message, $matches)) {
                $message = $matches[1];
            }

            throw new CouldNotInterpretXmlResponse($message, $e->getCode(), $e);
        }
    }

    /**
     * Returns combined error message to pass to exception
     *
     * @return string
     */
    protected function getLibXmlErrorMessage()
    {
        $errors = [];

        /** @var \LibXMLError $libError */
        foreach (libxml_get_errors() as $libError) {

            $errors[] = $libError->message
                      . "(lev: {$libError->level}, line/col: {$libError->line} / {$libError->column})";
        }

        return implode('; ', $errors);
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
