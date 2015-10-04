<?php
namespace Czim\Service\Contracts;

use Czim\DataObject\Contracts\DataObjectInterface;

interface ServiceResponseInterface extends DataObjectInterface
{
    /**
     * @param mixed $data
     */
    public function setData($data);

    /**
     * @return mixed
     */
    public function getData();

    /**
     * @param int $code
     */
    public function setStatusCode($code);

    /**
     * @return int
     */
    public function getStatusCode();

    /**
     * @param string $error
     */
    public function setError($error);

    /**
     * @return string
     */
    public function getError();

}
