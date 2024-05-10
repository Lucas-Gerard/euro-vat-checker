<?php

namespace LucasG\EuroVatChecker;

class EuroVatCheckerException extends \Exception
{
    public static function fromErrorResponse(mixed $responseData): self
    {
        $message = 'Request failed with reason: ' . json_encode($responseData);

        return new self($message);
    }
}
