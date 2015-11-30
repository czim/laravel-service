<?php
namespace Czim\Service\Contracts;

use Czim\Service\Exceptions\CouldNotInterpretXmlResponseException;

interface XmlParserInterface
{

    /**
     * Parses and/or cleans XML content into a (normalized) data format
     *
     * @param string $xml   Raw XML content
     * @return mixed
     * @throws CouldNotInterpretXmlResponseException
     */
    public function parse($xml);

}
