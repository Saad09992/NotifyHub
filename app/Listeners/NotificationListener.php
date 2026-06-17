<?php

namespace App\Listeners;

use App\Events\NotificationEvent;
use App\Jobs\SendNotificationJob;
use App\Services\NotificationService;
use App\Services\RecipientService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
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
        foreach ($event->data['channels'] as $channel){
            $event->data['channel']=$channel;
            SendNotificationJob::dispatch($event->data);
        }
    }
}
