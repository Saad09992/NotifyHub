<?php

namespace App\Contracts;

use App\Exceptions\SendNotificationFailedException;

interface NotificationContract{
    /**
     * @param  string  $message
     * @param $contact
     * @throws SendNotificationFailedException
     */
    public function sendNotification($contact, string $message):string;
}