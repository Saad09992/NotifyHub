<?php

namespace App\Exceptions;

use Exception;

class SendNotificationFailedException extends Exception
{
    public function render():string {
        return response($this->message,400);
    }
}