<?php


namespace App\Exceptions;


use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SlashCommandException extends \Exception
{
    public function __construct(
        $message = "",
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, Response::HTTP_PRECONDITION_FAILED, $previous);
    }
}
