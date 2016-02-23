<?php
namespace Czim\Service\Exceptions;

use Exception;

class CouldNotRetrieveException extends Exception
{
    /**
     * @var array
     */
    protected $errors = [];


    /**
     * @param array $errors
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}
