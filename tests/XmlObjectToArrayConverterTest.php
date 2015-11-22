<?php
namespace Czim\Service\Test;

use Czim\Service\Interpreters\Xml\XmlObjectToArrayConverter;

class XmlObjectToArrayConverterTest extends TestCase
{

    /**
     * @test
     */
    function it_converts_simple_xml_object_to_array()
    {
        $converter = new XmlObjectToArrayConverter();

        $result = $converter->convert( $this->xml->getSimpleXmlElement() );

        $this->assertArraySubset(
            $this->xml->getMinimalXmlContentAsArray(),
            $result,
            "Incorrect converted array data"
        );
    }

}
