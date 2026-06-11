<?php

namespace App\Channels;

use App\Contracts\NotificationContract;
use App\Exceptions\SendNotificationFailedException;
use Illuminate\Support\Facades\Mail;

use function PHPUnit\Framework\isString;

class EmailChannel implements NotificationContract
{
    /**
     * @param  string  $message
     * @param $contact
     * @return bool
     * @throws SendNotificationFailedException
     */
    public function sendNotification(string $message,$contact): bool {
        if (isString($contact)){
            $contact = json_decode($contact,true);
        }
        
        if (!isset($contact['email'])){
            throw new SendNotificationFailedException('Invalid Contact Details Received. Missing Key email');
        }
        try {
            Mail::raw($message, function ($message) use ($contact) {
                $message->to($contact['email'])
                    ->subject("Notification from NotifyHub");
            });
            
            return true;
        }catch (SendNotificationFailedException $e){
            throw new SendNotificationFailedException("Failed to send notification to Email || Recipient: $contact || Message: $message");
        }
    }
}