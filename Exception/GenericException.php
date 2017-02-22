<?php
namespace Application\Exception;

use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * basic Exception extension to automate meta in message
 * not production
 *
 * Class GenericException
 * @package Application\Exception
 */
class GenericException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        $message = debug_backtrace()[1]['class'] . '::' . debug_backtrace()[1]['function']
            . 'Says: ' . $message
            . ' (line:  ' . debug_backtrace()[1]['line'] . ') ';
        parent::__construct($message, $code, $previous);
    }
}