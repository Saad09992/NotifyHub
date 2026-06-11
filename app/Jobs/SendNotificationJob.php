<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;
    
    private $data;
    private NotificationService $notificationService;
    /**
     * Create a new job instance.
     */
    public function __construct(NotificationService $notification_service,$data)
    {
        $this->notificationService = $notification_service;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $channel = $this->notificationService->resolveChannel($this->data['channel']);
        $sent = $channel->sendNotification($this->data['message'],$this->data['recipient']['contact_details']);
    }
}
