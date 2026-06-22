<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'recipient_id' => $this->id,
            'contact_details' => $this->contact_details,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
