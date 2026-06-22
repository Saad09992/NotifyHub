<?php

namespace App\Listeners;

use App\Events\NotificationEvent;
use App\Jobs\SendNotificationJob;
use App\Jobs\UpdateNotificationStatusJob;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;


class NotificationListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(NotificationEvent $event): void
    {
        $jobs = [];
        foreach ($event->data['channels'] as $channel){
            $data = $event->data;
            $data['channel']=$channel;
            $jobs[] = new SendNotificationJob($data);
        }
        
        $notificationId = $event->data['notification_id'];
        
        Bus::batch($jobs)->finally(function (Batch $batch)use ($notificationId){
            UpdateNotificationStatusJob::dispatch($notificationId);
        })->dispatch();
    }
}
