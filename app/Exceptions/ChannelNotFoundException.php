<?php

namespace App\Exceptions;

use Exception;

class ChannelNotFoundException extends Exception
{
    public function render():string {
        return response($this->message,400);
    }
}