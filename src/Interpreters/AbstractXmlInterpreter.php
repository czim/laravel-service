<?php

namespace Czim\Service\Interpreters;

use Czim\Service\Contracts\XmlObjectConverterInterface;

abstract class AbstractXmlInterpreter extends AbstractInterpreter
{
    /**
     * @var XmlObjectConverterInterface
     */
    protected $xmlConverter;


    public function __construct(XmlObjectConverterInterface $xmlConverter = null)
    {
        if ($xmlConverter === null) {
            $xmlConverter = app(XmlObjectConverterInterface::class);
        }

        $this->xmlConverter = $xmlConverter;

        parent::__construct();
    }
}
