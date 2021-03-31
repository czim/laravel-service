<?php

namespace Czim\Service\Contracts;

interface XmlObjectConverterInterface
{
    /**
     * Converts XML object to array.
     *
     * @param object $object object or SimpleXml object to clean/convert to array
     * @return array
     */
    public function convert(object $object): array;
}
