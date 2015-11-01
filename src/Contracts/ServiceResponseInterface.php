<?php
namespace Czim\Service\Contracts;

use Czim\DataObject\Contracts\DataObjectInterface;

interface ServiceResponseInterface extends DataObjectInterface
{

    /**
     * Sets the response data
     *
     * @param mixed $data
     * @return $this
     */
    public function setData($data);

    /**
     * Returns the response data
     *
     * @return mixed
     */
    public function getData();

    /**
     * Sets the HTTP or other service status code
     *
     * @param int $code
     * @return $this
     */
    public function setStatusCode($code);

    /**
     * Returns the HTTP or other service status code
     *
     * @return int
     */
    public function getStatusCode();

    /**
     * Returns all errors listed
     *
     * @return array
     */
    public function getErrors();

    /**
     * Sets all errors at once
     *
     * @param array $errors
     * @return $this
     */
    public function setErrors(array $errors);

    /**
     * Adds a single error to the error list
     *
     * @param string $error
     * @return $this
     */
    public function addError($error);

    /**
     * Returns succesfulness state of request
     *
     * @return bool
     */
    public function getSuccess();

    /**
     * Sets succesfulness state
     *
     * @param bool $success
     * @return $this
     */
    public function setSuccess($success);

}
