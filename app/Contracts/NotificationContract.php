<?php

namespace App\Contracts;

use App\Exceptions\SendNotificationFailedException;

interface NotificationContract{
    /**
     * @param  string  $message
     * @param $contact
     * @return bool
     * @throws SendNotificationFailedException
     */
    public function sendNotification(string $contact, string $message):string;
}