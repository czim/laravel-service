<?php

namespace Czim\Service\Interpreters;

use Czim\Service\Contracts\XmlObjectConverterInterface;
use Czim\Service\Contracts\XmlParserInterface;

/**
 * Interprets raw XML string response data
 */
class BasicRawXmlInterpreter extends AbstractXmlInterpreter
{
    /**
     * Whether to decode as an associative array
     *
     * @var bool
     */
    protected $asArray = false;

    /**
     * @var XmlParserInterface
     */
    protected $xmlParser;


    /**
     * @param bool|null                        $asArray
     * @param XmlParserInterface|null          $xmlParser
     * @param XmlObjectConverterInterface|null $xmlConverter
     */
    public function __construct(
        ?bool $asArray = null,
        XmlParserInterface $xmlParser = null,
        XmlObjectConverterInterface $xmlConverter = null
    ) {
        if ($asArray !== null) {
            $this->asArray = $asArray;
        }

        if ($xmlParser === null) {
            $xmlParser = app(XmlParserInterface::class);
        }

        $this->xmlParser = $xmlParser;

        parent::__construct($xmlConverter);
    }


    protected function doInterpretation(): void
    {
        $this->interpretedResponse->setSuccess(
            $this->responseInformation->getStatusCode() > 199
            && $this->responseInformation->getStatusCode() < 300
        );

        $this->response = $this->xmlParser->parse($this->response);

        if ($this->asArray) {
            $this->response = $this->xmlConverter->convert($this->response);
        }

        $this->interpretedResponse->setData(
            $this->response
        );
    }
}
