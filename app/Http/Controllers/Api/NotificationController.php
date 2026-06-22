<?php

namespace App\Http\Controllers\Api;

use App\Events\NotificationEvent;
use App\Exceptions\RecipientNotFoundException;
use App\Exceptions\TemplateNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendNotificationRequest;
use App\Models\Notification;
use App\Models\Template;
use App\Services\NotificationService;
use App\Services\RecipientService;
use App\Services\TemplateService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    private NotificationService $notificationService;
    private RecipientService $recipientService;
    private TemplateService $templateService;
    
    public function __construct(NotificationService $notification_service, RecipientService $recipient_service, 
        TemplateService $template_service){
        $this->notificationService = $notification_service;
        $this->recipientService = $recipient_service;
        $this->templateService = $template_service;
    }

    /**
     * @throws TemplateNotFoundException
     * @throws RecipientNotFoundException
     */
    public function SendNotification(SendNotificationRequest $request){
            $validated = $request->validated();
            try {
                $recipient = $this->recipientService->getRecipient($validated['recipient_id']);
                $template = $this->templateService->getTemplate($validated['template_id']);
                
                $notificationEntry = $this->notificationService->addNotificationEntry(
                    $recipient->id,
                    $validated['template_id'],
                    $validated['payload'],
                    $validated['channels']
                );

                $composedMessage = $this->templateService->compose($template['template_body'], $validated['payload']);

                $validated['recipient'] = $recipient;
                $validated['notification_id'] = $notificationEntry->id;
                $validated['message'] = $composedMessage;

                event(new NotificationEvent($validated));

                return response()->json(['message' => 'Notification added to queue', 'status' => 'success']);
            }catch(RecipientNotFoundException | TemplateNotFoundException $e){
                return response()->json(['message'=>'Failed to queue Notification','status'=>'failure',
                    'error'=>$e->getMessage()],404);
            }
    }
}