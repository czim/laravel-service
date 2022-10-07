<?php

declare(strict_types=1);

namespace Czim\Service\Interpreters;

use Czim\Service\Contracts\XmlObjectConverterInterface;

abstract class AbstractXmlInterpreter extends AbstractInterpreter
{
    protected XmlObjectConverterInterface $xmlConverter;


    public function __construct(XmlObjectConverterInterface $xmlConverter = null)
    {
        if ($xmlConverter === null) {
            $xmlConverter = app(XmlObjectConverterInterface::class);
        }

        $this->xmlConverter = $xmlConverter;

        parent::__construct();
    }
}
