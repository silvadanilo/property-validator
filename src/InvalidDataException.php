<?php

declare(strict_types=1);

namespace PropertyValidator;

use Throwable;

class InvalidDataException extends \RuntimeException
{
    /** @var mixed */
    private $errorList;

    /**
     * @param mixed $errorList
     * @param string $message [optional] The Exception message to throw.
     * @param int $code [optional] The Exception code.
     * @param Throwable $previous [optional] The previous throwable used for the exception chaining.
     */
    public function __construct($errorList, $message = "", $code = 0, Throwable $previous = null)
    {
        $this->errorList = $errorList;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return mixed
     */
    public function getErrorList()
    {
        return $this->errorList;
    }
}
