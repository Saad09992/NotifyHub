<?php

namespace App\Services;

use App\Exceptions\RecipientNotFoundException;
use App\Exceptions\TemplateNotFoundException;
use App\Models\Recipient;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class RecipientService
{
    /**
     * @throws RecipientNotFoundException
     */
    public function getRecipient(string $recipient_id): Recipient{
        try{
            return Recipient::findOrFail($recipient_id);
        }catch (ModelNotFoundException $e){
            throw new RecipientNotFoundException($recipient_id, previous: $e);
        }
    }
    
    public function saveRecipient($data): Recipient{
        return Recipient::create([
            'user_id'=>Auth::user()->id,
            'contact_details'=>json_encode($data['contact_details'])
        ]);
    }
}