<?php

namespace App\Services;

use App\Exceptions\RecipientNotFoundException;
use App\Models\Recipient;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class RecipientService
{
    /**
     * @throws RecipientNotFoundException
     */
    public function getRecipient(string $recipient_id): Recipient
    {
        try {
            $recipient = Recipient::findOrFail($recipient_id);
        } catch (ModelNotFoundException $e) {
            throw new RecipientNotFoundException($recipient_id, previous: $e);
        }

        if ($recipient->user_id !== Auth::id()) {
            throw new RecipientNotFoundException($recipient_id);
        }

        return $recipient;
    }

    public function listRecipients(): Collection
    {
        return Recipient::where('user_id', Auth::id())->get();
    }

    /**
     * @throws RecipientNotFoundException
     */
    public function deleteRecipient(Recipient $recipient): void
    {
        if ($recipient->user_id !== Auth::id()) {
            throw new RecipientNotFoundException((string) $recipient->id);
        }

        $recipient->delete();
    }
    
    public function saveRecipient($data): Recipient
    {
        return Recipient::create([
            'user_id' => Auth::id(),
            'contact_details' => $data['contact_details'],
        ]);
    }
    
    /**
     * @throws RecipientNotFoundException
     */
    public function updateRecipient(array $data, Recipient $recipient): Recipient
    {
        if ($recipient->user_id !== Auth::id()) {
            throw new RecipientNotFoundException((string) $recipient->id);
        }

        if (isset($data['contact_details'])) {
            $existing = $recipient->contact_details ?? [];
            $recipient->contact_details = array_merge($existing, $data['contact_details']);
        }

        $recipient->save();

        return $recipient;
    }
}