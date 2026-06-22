<?php

namespace App\Channels;

use App\Contracts\NotificationContract;
use App\Exceptions\SendNotificationFailedException;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;


class EmailChannel implements NotificationContract
{
    /**
     * @param  string  $message
     * @param $contact
     * @throws SendNotificationFailedException
     */
    public function sendNotification($contact,string $message): string {
        if (is_string($contact)){
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
            
            return 'delivered';
        }catch (TransportExceptionInterface $e){
            throw new SendNotificationFailedException("Failed to send notification to Email || Recipient: 
            {$contact['email']} 
|| Message: {$e->getMessage()}",0,$e);
        }
    }
}