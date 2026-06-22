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
    public function sendNotification($contact, string $message): string {
        if (is_string($contact)){
            $contact = json_decode($contact,true);
        }
        if (!isset($contact['slack_webhook'])){
            throw new SendNotificationFailedException('Invalid Contact Details Received. Missing Key slack_webhook');
        }
        try {
            $resp = Http::post($contact['slack_webhook'],[
                'text'=>$message
            ]);
            
            if(!$resp->successful()){
                throw new SendNotificationFailedException("Failed to send notification to slack || Message: $message Error: HTTP error: SlackChannel:23");
            }
            
            return "delivered";
        }catch (ConnectionException $e) {
            throw new SendNotificationFailedException(
                "Failed to send notification to slack || Message: $message || Error: {$e->getMessage()}", 0, $e
            );
        }
    }
}   