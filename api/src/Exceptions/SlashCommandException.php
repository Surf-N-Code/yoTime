<?php


namespace App\Exceptions;


use Throwable;

class SlashCommandException extends \Exception
{
    public function __construct(
        $message = "",
        $code = 0,
        Throwable $previous = null
    ) {
        $this->code = 412;
        parent::__construct($message, $code, $previous);
    }
}
