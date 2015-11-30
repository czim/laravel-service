<?php
namespace Czim\Service\Exceptions;

class CouldNotInterpretResponseException extends \Exception
{

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @param null       $message
     * @param array      $errors    list of errors encountered while parsing, validating or attempting to interpret
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($message = null, $errors = [], $code = 0, $previous = null)
    {
        $this->errors = $errors;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}
