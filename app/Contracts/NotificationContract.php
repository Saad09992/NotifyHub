<?php

namespace App\Contracts;

interface NotificationContract{
    public function sendNotification(string $contact, string $message):string;
}