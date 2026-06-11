<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveRecipientRequest;
use App\Http\Resources\SaveRecipientResource;
use App\Services\RecipientService;

class RecipientController extends Controller{
    
    private RecipientService $recipientService;
     
    public function __construct(RecipientService $recipient_service) {
        $this->recipientService = $recipient_service;
    }

    public function saveRecipient(SaveRecipientRequest $request){
        $validated = $request->validated();
        
        $recipient = $this->recipientService->saveRecipient($validated);
        
        return new SaveRecipientResource($recipient);
    }
}