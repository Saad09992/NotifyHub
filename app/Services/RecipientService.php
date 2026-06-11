<?php

namespace App\Services;

use App\Models\Recipient;
use Illuminate\Support\Facades\Auth;

class RecipientService
{
    public function getRecipient($id){
        return Recipient::findOrFail($id);
    }
    
    public function saveRecipient($data): Recipient{
        return Recipient::create([
            'user_id'=>Auth::user()->id,
            'contact_details'=>json_encode($data['contact_details'])
        ]);
    }
}