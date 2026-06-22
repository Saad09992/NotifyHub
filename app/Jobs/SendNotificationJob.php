<?php

namespace App\Jobs;

use App\Exceptions\ChannelNotSupportedException;
use App\Exceptions\SendNotificationFailedException;
use App\Models\NotificationAttempt;
use App\Services\NotificationService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;

class SendNotificationJob implements ShouldQueue
{
    use Queueable, Batchable;
    public int $tries = 3;
    public int $timeout = 15;
    
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
        $this->notificationAttempt = $notification_service->addNotificationAttemptEntry($this->data['recipient']['id'], $this->data['notification_id'],$this->data['channel']);
        try {
            $channel = $notification_service->resolveChannel($this->data['channel']);
            $status = $channel->sendNotification($this->data['recipient']['contact_details'],$this->data['message']);
            $this->notificationAttempt->update([
                'status'=>$status
            ]);
            $this->notificationAttempt->save();
        }catch (ChannelNotSupportedException $e){
            $this->notificationAttempt->update([
                'status'=>'failed',
                'failure_reason'=>$e->getMessage()
            ]);  
            $this->fail($e);
        }catch (SendNotificationFailedException $e) {
            $this->notificationAttempt->update([
                'status'=>'failed',
                'failure_reason'=>$e->getMessage()
            ]);
            $this->notificationAttempt->save();
            throw $e;
        }
    }
    
    public function failed(\Throwable $e): void{
        Log::critical('SendNotificationJob permanently failed', [
            'notification_id' => $this->data['notification_id'] ?? null,
            'channel' => $this->data['channel'] ?? null,
            'exception' => $e->getMessage(),
        ]);
    }
}
