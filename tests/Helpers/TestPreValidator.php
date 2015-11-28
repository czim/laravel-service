<?php
namespace Czim\Service\Test\Helpers;

use Czim\Service\Interpreters\Decorators\AbstractValidationPreDecorator;

class TestPreValidator extends AbstractValidationPreDecorator
{

    /**
     * Validates the (raw) response
     *
     * @param mixed $response
     * @return bool
     */
    protected function validate($response)
    {
        if ($response == 'wrong') {

            $this->errors[] = 'wrong response';
            return false;
        }

        return true;
    }

}
