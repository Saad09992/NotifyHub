<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'template_id' => $this->id,
            'template_body' => $this->template_body,
            'supported_channels' => $this->supported_channels,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
