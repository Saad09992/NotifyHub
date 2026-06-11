<?php

namespace App\Channels;

use App\Contracts\NotificationContract;
use App\Exceptions\SendNotificationFailedException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class SlackChannel implements NotificationContract
{
    /**
     * @throws SendNotificationFailedException
     * @throws ConnectionException
     */
    public function sendNotification(string $contact, string $message): string {
        try {
            $resp = Http::post($contact,[
                'text'=>$message
            ]);
            
            if(!$resp->successful()){
                throw new SendNotificationFailedException("Failed to send notification to slack || Recipient: $contact || Message: $message Error: HTTP error: SlackChannel:23");
            }
            
            return "delivered";
        }catch (SendNotificationFailedException $e){
            throw new SendNotificationFailedException("Failed to send notification to slack || Recipient: $contact || Message: $message || Error: $e");
        }
    }
}   