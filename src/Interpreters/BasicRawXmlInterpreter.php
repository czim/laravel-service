<?php
namespace Czim\Service\Interpreters;

/**
 * Interprets raw XML string response data
 */
class BasicRawXmlInterpreter extends AbstractXmlInterpreter
{
    // whether to remove CDATA tags to get all content normally
    const STRIP_CDATA_TAGS = true;

    /**
     * Whether to decode as an associative array
     *
     * @var bool
     */
    protected $asArray = false;


    /**
     * @param bool|null $asArray
     */
    public function __construct($asArray = null)
    {
        if ( ! is_null($asArray)) {
            $this->asArray = $asArray;
        }

        parent::__construct();
    }


    protected function doInterpretation()
    {
        $this->interpretedResponse->setSuccess(
            $this->responseInformation->getStatusCode() == 200
        );

        $this->response = $this->parseXml($this->response);

        // if configured to, use the clunky way to build an array from the object
        if ($this->asArray) {
            $this->response = $this->convertXmlObjectToArray($this->response);
        }

        $this->interpretedResponse->setData(
            $this->response
        );
    }

    /**
     * Parses XML string
     *
     * @param  string $xml
     * @return object
     */
    protected function parseXml($xml)
    {
        if (self::STRIP_CDATA_TAGS) {

            return simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        }

        return simplexml_load_string($xml);
    }

    /**
     * In special cases where XML is malformed, attempt to salvage the
     * last element anyway.
     *
     * @param  string $xml
     * @return array
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

        return $this->parseXml($xml);
    }
}
