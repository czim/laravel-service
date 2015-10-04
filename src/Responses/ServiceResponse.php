<?php
namespace Czim\Service\Responses;

use Czim\DataObject\AbstractDataObject;
use Czim\Service\Contracts\ServiceResponseInterface;

class ServiceResponse extends AbstractDataObject implements ServiceResponseInterface
{

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->setAttribute('data', $data);
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->getAttribute('data');
    }

    /**
     * @param int $code
     */
    public function setStatusCode($code)
    {
        $this->setAttribute('statusCode', $code);
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->getAttribute('statusCode') ?: 0;
    }

    /**
     * @param string $error
     */
    public function setError($error)
    {
        $this->setAttribute('error', $error);
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->getAttribute('error');
    }

}
