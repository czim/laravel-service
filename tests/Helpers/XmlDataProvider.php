<?php
namespace Czim\Service\Test\Helpers;

use SimpleXMLElement;

class XmlDataProvider
{
    /**
     * @return SimpleXmlElement
     */
    public function getSimpleXmlElement()
    {
        return new SimpleXmlElement($this->getMinimalValidXmlContent());
    }

    /**
     * @return string
     */
    public function getMinimalValidXmlContent()
    {
        return <<<MINIMALXML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>Minimal XHTML 1.0 Document</title>
</head>
<body>
<p>This is a minimal document.</p>
</body>
</html>
MINIMALXML;
    }

    /**
     * @return array
     */
    public function getMinimalXmlContentAsArray()
    {
        return [
            "@attributes" => [
                "lang" => "en"
            ],
            "head" => [
                "title" => "Minimal XHTML 1.0 Document"
            ],
            "body" => [
                "p" => "This is a minimal document."
            ],
        ];
    }


    /**
     * @return string
     */
    public function getInvalidXmlContent()
    {
        return <<<INVALIDXML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>
<html>
INVALIDXML;
    }


    /**
     * @return string
     */
    public function getXmlWithRelativeNamespaces()
    {
        return <<<BROKENNAMESPACEXML
<?xml version="1.0" encoding="UTF-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
    <s:Body>
        <GetProductResponse xmlns="Unit4.AgressoWholesale.Connectors">
            <GetProductResult xmlns:a="http://schemas.datacontract.org/2004/07/Unit4.AgressoWholesale.ProductConnector.Contracts" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                <a:ProfileCode>WEBSITE</a:ProfileCode>
            </GetProductResult>
        </GetProductResponse>
    </s:Body>
</s:Envelope>
BROKENNAMESPACEXML;

    }

    /**
     * @return string
     */
    public function getXmlWithRelativeNamespacesFixed()
    {
        return <<<FIXEDNAMESPACEXML
<?xml version="1.0" encoding="UTF-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
    <s:Body>
        <GetProductResponse xmlns="http://Unit4.AgressoWholesale.Connectors">
            <GetProductResult xmlns:a="http://schemas.datacontract.org/2004/07/Unit4.AgressoWholesale.ProductConnector.Contracts" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                <a:ProfileCode>WEBSITE</a:ProfileCode>
            </GetProductResult>
        </GetProductResponse>
    </s:Body>
</s:Envelope>
FIXEDNAMESPACEXML;
    }


    /**
     * Note that this is a bit crummy, since it tests against spaces being left in the XML while
     * stripping the namespaces. Not a high priority, but this test should really be fixed,
     * and perhaps be RegEx-based to be indifferent to spaces in the tags.
     *
     * @return string
     */
    public function getXmlWithNamespacesRemoved()
    {
        return <<<FIXEDNAMESPACEXML
<?xml version="1.0" encoding="UTF-8"?>
<Envelope >
    <Body>
        <GetProductResponse >
            <GetProductResult  >
                <ProfileCode>WEBSITE</ProfileCode>
            </GetProductResult>
        </GetProductResponse>
    </Body>
</Envelope>
FIXEDNAMESPACEXML;
    }
}
