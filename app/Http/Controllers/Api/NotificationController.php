<?php

namespace App\Http\Controllers\Api;

use App\Events\NotificationEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendNotificationRequest;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Services\RecipientService;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    private NotificationService $notificationService;
    private RecipientService $recipientService;
    
    public function __construct(NotificationService $notification_service, RecipientService $recipient_service){
        $this->notificationService = $notification_service;
        $this->recipientService = $recipient_service;
    }
    
    public function SendNotification(SendNotificationRequest $request){
            $validated = $request->validated();

            $recipient = $this->recipientService->getRecipient($validated['recipient_id']);

            $notificationEntry = $this->notificationService->addNotificationEntry(
                $recipient->id,
                $validated['template_id'],
                $validated['payload'],
                $validated['channels']
            );

            $message = $this->notificationService->composeMessage($validated['template_id'], $validated['payload']);

            $validated['recipient'] = $recipient;
            $validated['notification_id'] = $notificationEntry->id;
            $validated['message'] = $message;

            event(new NotificationEvent($validated));
        
            return response()->json(['message'=>'Notification added to queue','status'=>'success']);
    }
}