<?php
namespace Czim\Service\Interpreters;

use Czim\Service\Contracts\XmlObjectConverterInterface;

abstract class AbstractXmlInterpreter extends AbstractInterpreter
{
    /**
     * @var XmlObjectConverterInterface
     */
    protected $xmlConverter;


    /**
     * @param XmlObjectConverterInterface|null $xmlConverter
     */
    public function __construct(XmlObjectConverterInterface $xmlConverter = null)
    {
        if (is_null($xmlConverter)) {
            $xmlConverter = app(XmlObjectConverterInterface::class);
        }

        $this->xmlConverter = $xmlConverter;

        parent::__construct();
    }

}
