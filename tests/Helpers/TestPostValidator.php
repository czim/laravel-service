<?php
namespace Czim\Service\Test\Helpers;

use Czim\Service\Contracts\ServiceResponseInterface;
use Czim\Service\Interpreters\Decorators\AbstractValidationPostDecorator;

class TestPostValidator extends AbstractValidationPostDecorator
{

    /**
     * Validates the ServiceResponse
     *
     * @param ServiceResponseInterface $response
     * @return bool
     */
    protected function validateResponse(ServiceResponseInterface $response)
    {
        if ($response->getData() == 'wrong') {

            $this->errors[] = 'wrong response';
            return false;
        }

        return true;
    }

}
