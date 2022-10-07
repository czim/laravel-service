<?php
declare(strict_types=1);


namespace Czim\Service\Test\Helpers;

use Czim\Service\Interpreters\AbstractInterpreter;

class TestMockInterpreter extends AbstractInterpreter
{
    /**
     * Handles the interpretation
     * This should update/modify the interpretedResponse property
     */
    protected function doInterpretation(): void
    {
        $this->interpretedResponse->setSuccess(true);

        $this->interpretedResponse->setData($this->response);
    }
}
