<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateNotificationStatusJob implements ShouldQueue
{
    use Queueable;
    private $notificationId;
    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->notificationId = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notification_service): void
    {
        $notification_service->updateNotificationStatus($this->notificationId);
    }
}
