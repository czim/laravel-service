<?php
namespace Czim\DataObject\Test\Helpers;

use Czim\Service\Interpreters\AbstractInterpreter;

class TestMockInterpreter extends AbstractInterpreter
{

    /**
     * Handles the interpretation
     * This should update/modify the interpretedResponse property
     */
    protected function doInterpretation()
    {
        $this->interpretedResponse->setSuccess(true);

        $this->interpretedResponse->setData($this->response);
    }
}
