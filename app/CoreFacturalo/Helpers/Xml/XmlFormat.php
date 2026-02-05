<?php
namespace App\CoreFacturalo\Helpers\Xml;

class XmlFormat
{
    public static function format($xml, $formatOutput = TRUE, $declaration = TRUE)
    {
        $sxe = ($xml instanceof \SimpleXMLElement) ? $xml : simplexml_load_string($xml);
        $domElement = dom_import_simplexml($sxe);
        $domDocument = $domElement->ownerDocument;
        $domDocument->preserveWhiteSpace = false;
        $domDocument->formatOutput = (bool)$formatOutput;

        // Asegurar que el encoding sea UTF-8
        $domDocument->encoding = 'UTF-8';

        $domDocument->loadXML($sxe->asXML(), LIBXML_NOBLANKS);

        return (bool)$declaration ? $domDocument->saveXML() : $domDocument->saveXML($domDocument->documentElement);
    }
}