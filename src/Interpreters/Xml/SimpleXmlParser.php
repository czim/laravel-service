<?php

declare(strict_types=1);

namespace Czim\Service\Interpreters\Xml;

use Czim\Service\Contracts\XmlParserInterface;
use Czim\Service\Exceptions\CouldNotInterpretXmlResponseException;
use ErrorException;
use SimpleXMLElement;

class SimpleXmlParser implements XmlParserInterface
{
    // Whether to remove CDATA tags to get all content normally.
    protected const STRIP_CDATA_TAGS = true;


    /**
     * @param string $xml
     * @return SimpleXMLElement
     * @throws CouldNotInterpretXmlResponseException
     */
    public function parse(string $xml): SimpleXMLElement
    {
        // note that this resets the PHP error handler -- if anything goes wrong,
        // look here first
        libxml_use_internal_errors(true);

        try {
            $parsed = (self::STRIP_CDATA_TAGS)
                ? simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)
                : simplexml_load_string($xml);

            if (! $parsed) {
                throw new CouldNotInterpretXmlResponseException(
                    $this->getLibXmlErrorMessage(),
                    $this->getLibXmlErrors()
                );
            }

            libxml_clear_errors();

            return $parsed;
        } catch (ErrorException $e) {
            $message = $e->getMessage();

            if (preg_match('#^\s*simplexml_load_string\(\):\s*(.*)$#i', $message, $matches)) {
                $message = $matches[1];
            }

            throw new CouldNotInterpretXmlResponseException($message, [ $message ], $e->getCode(), $e);
        }
    }


    /**
     * Returns combined error message to pass to exception.
     *
     * @return string
     */
    protected function getLibXmlErrorMessage(): string
    {
        return implode('; ', $this->getLibXmlErrors());
    }

    /**
     * Returns errors encountered by libxml.
     *
     * @return string[]
     */
    protected function getLibXmlErrors(): array
    {
        $errors = [];

        foreach (libxml_get_errors() as $libError) {
            $errors[] = $libError->message
                . "(lev: {$libError->level}, line/col: {$libError->line} / {$libError->column})";
        }

        return $errors;
    }
}
