<?php
namespace Czim\Service\Services;

use BeSimple\SoapClient\SoapClient;

class BeSimpleSoapService extends SoapService
{

    protected $soapClientClass = SoapClient::class;

}
