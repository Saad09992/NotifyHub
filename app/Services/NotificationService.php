<?php

namespace App\Services;

use App\Channels\EmailChannel;
use App\Channels\SlackChannel;
use App\Contracts\NotificationContract;
use App\Exceptions\ChannelNotSupportedException;
use App\Models\Notification;
use App\Models\NotificationAttempt;
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
    

    /**
     * @throws ChannelNotSupportedException
     */
    public function resolveChannel(string $name): NotificationContract{
        return match ($name){
          'email'=> app(EmailChannel::class),
          'slack'=>app(SlackChannel::class),
          default=> throw new ChannelNotSupportedException($name)  
        };
    }
    
    public function updateNotificationStatus($notification_id): void {
        $success = 0;
        $failure = 0;
        $notification = Notification::with('notificationAttempts')->findOrFail($notification_id);
        
        foreach ($notification->notificationAttempts as $attempt){
            if ($attempt->status == 'delivered') {
                $success++;
            }else{
                $failure++;
            }
        }

        $status = match (true) {
            $notification->notificationAttempts->isEmpty() => 'pending',
            $success > 0 && $failure === 0 => 'success',
            $success === 0 && $failure > 0 => 'failure',
            $success > 0 && $failure > 0 => 'partial_success',
            default => 'failure',
        };
        
        $notification->status = $status;
        $notification->save();
    }
}