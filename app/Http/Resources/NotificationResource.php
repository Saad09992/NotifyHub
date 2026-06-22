<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'notification_id' => $this->id,
            'recipient_id' => $this->recipient_id,
            'template_id' => $this->template_id,
            'channels' => $this->channels,
            'payload' => $this->payload,
            'status' => $this->status,
            'attempts' => $this->whenLoaded('notificationAttempts', function () {
                return $this->notificationAttempts->map(function ($attempt) {
                    return [
                        'channel' => $attempt->channel,
                        'status' => $attempt->status,
                        'failure_reason' => $attempt->failure_reason,
                        'created_at' => $attempt->created_at,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
