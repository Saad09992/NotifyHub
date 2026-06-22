<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveRecipientRequest;
use App\Http\Requests\UpdateRecipientRequest;
use App\Http\Resources\RecipientResource;
use App\Http\Resources\SaveRecipientResource;
use App\Http\Resources\UpdateRecipientResource;
use App\Models\Recipient;
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
    
    public function updateRecipient(UpdateRecipientRequest $request, Recipient $recipient)
    {
        $validated = $request->validated();

        $recipient = $this->recipientService->updateRecipient($validated, $recipient);

        return new UpdateRecipientResource($recipient);
    }

    public function listRecipients()
    {
        return RecipientResource::collection($this->recipientService->listRecipients());
    }

    public function showRecipient(string $id)
    {
        $recipient = $this->recipientService->getRecipient($id);

        return new RecipientResource($recipient);
    }

    public function deleteRecipient(Recipient $recipient)
    {
        $this->recipientService->deleteRecipient($recipient);

        return response()->json([
            'status' => 'success',
            'message' => 'Recipient deleted Successfully',
        ]);
    }
}