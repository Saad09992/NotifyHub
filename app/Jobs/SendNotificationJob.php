<?php

namespace App\Jobs;

use App\Exceptions\SendNotificationFailedException;
use App\Models\NotificationAttempt;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;
    
    private $data;
    private NotificationAttempt $notificationAttempt;
    
    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notification_service): void
    {
        $channel = $notification_service->resolveChannel($this->data['channel']);
        try {
            $this->notificationAttempt = $notification_service->addNotificationAttemptEntry($this->data['recipient']['id'], $this->data['notification_id'],$this->data['channel']);
            $status = $channel->sendNotification($this->data['message'], $this->data['recipient']['contact_details']);
            $this->notificationAttempt->update([
                'status'=>$status
            ]);
            $this->notificationAttempt->save();
        } catch (SendNotificationFailedException|ConnectionException $e) {
            $this->notificationAttempt->update([
                'status'=>'failed',
                'failure_reason'=>$e->getMessage()
            ]);
            $this->notificationAttempt->save();
        }

        
    }
}
