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
}
