<?php
namespace Czim\Service\Responses;

use Czim\DataObject\AbstractDataObject;
use Czim\Service\Contracts\ServiceResponseInterface;

/**
 * The processed/interpreted response data finally returned from the service call
 */
class ServiceResponse extends AbstractDataObject implements ServiceResponseInterface
{

    protected $magicAssignment = false;

    protected $attributes = [
        'data'       => null,
        'statusCode' => 0,
        'errors'     => [],
        'success'    => true,
    ];

    /**
     * @param mixed $data
     * @return $this
     */
    public function setData($data)
    {
        $this->setAttribute('data', $data);

        return $this;
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
     * @return $this
     */
    public function setStatusCode($code)
    {
        $this->setAttribute('statusCode', $code);

        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->getAttribute('statusCode') ?: 0;
    }


    /**
     * Returns all errors listed
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->getAttribute('errors');
    }

    /**
     * Sets all errors at once
     *
     * @param array $errors
     * @return $this
     */
    public function setErrors(array $errors)
    {
        $this->setAttribute('errors', $errors);

        return $this;
    }

    /**
     * Adds a single error to the error list
     *
     * @param string $error
     * @return $this
     */
    public function addError($error)
    {
        $errors = $this->getAttribute('errors') ?: [];

        $errors[] = $error;

        $this->setAttribute('errors', $errors);

        return $this;
    }

    /**
     * Returns succesfulness state of request
     *
     * @return bool
     */
    public function getSuccess()
    {
        return (bool) $this->getAttribute('success');
    }

    /**
     * Sets succesfulness state
     *
     * @param bool $success
     * @return $this
     */
    public function setSuccess($success)
    {
        $this->setAttribute('success', (bool) $success);

        return $this;
    }
}
