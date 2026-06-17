<?php

namespace App\Services;

use App\Channels\EmailChannel;
use App\Channels\SlackChannel;
use App\Models\Notification;
use App\Models\NotificationAttempt;
use http\Exception\InvalidArgumentException;
use Illuminate\Support\Facades\Auth;

class NotificationService
{

    public function __construct() {}
    
    public function addNotificationEntry(string $recipient_id, string $template_id,$payload,$channels):Notification{
        return Notification::create([
            'user_id'=>Auth::user()->id,
            'recipient_id'=>$recipient_id,
            'template_id'=>$template_id,
            'payload'=>$payload,
            'channels'=>$channels
        ]);
    }
    
    public function addNotificationAttemptEntry(string $recipient_id,string $notification_id,string $channel,?string 
    $status = 'pending', ?string $reason = null):NotificationAttempt{
        return NotificationAttempt::create([
            'notification_id'=>$notification_id,
            'channel'=>$channel,
            'status'=>$status ,
            'failure_reason'=>$reason
        ]);
    }
    
    public function composeMessage(string $template_id, $payload): string {
        return 'This is dummy message for now';
    }
    
    public function resolveChannel(string $name){
        return match ($name){
          'email'=> app(EmailChannel::class),
          'slack'=>app(SlackChannel::class),
          default=> throw new InvalidArgumentException("Channel [{$name}] is not supported")  
        };
    }
}