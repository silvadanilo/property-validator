<?php

declare(strict_types=1);

namespace PropertyValidator;

class InvalidDataException extends \RuntimeException
{
    private $errorList;

    public function __construct($errorList, $message = null, $code = 0, Exception $previous = null)
    {
        $this->errorList = $errorList;

        parent::__construct($message, $code, $previous);
    }

    public function getErrorList()
    {
        return $this->errorList;
    }
}
