<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaveRecipientResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'recipient_id'=>$this->id,
            'status'=>'success',
            'message'=>'Recipient details saved Successfully'
        ];
    }
}
