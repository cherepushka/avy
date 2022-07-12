<?php

namespace App\Model;

class Error
{

    public function __construct(
        private readonly string $message,
        private readonly mixed $details = null
    ){}

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getDetails(): mixed
    {
        return $this->details;
    }

}