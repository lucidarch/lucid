<?php

namespace Lucid\Exceptions;

use Exception;
use Illuminate\Validation\Validator as IlluminateValidator;

/**
 * An exception class that supports validators by extracting their messages
 * when given, or an array of messages as strings.
 */
class InvalidInputException extends Exception
{
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        if ($message instanceof IlluminateValidator) {
            $message = $message->messages()->all();
        }

        if (is_array($message)) {
            $message = implode("\n", $message);
        }

        parent::__construct($message, $code, $previous);
    }
}
