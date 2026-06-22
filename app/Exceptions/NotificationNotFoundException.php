<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class NotificationNotFoundException extends Exception
{
    private string $notificationId;

    public function __construct(string $notificationId, int $code = 0, ?Throwable $previous = null)
    {
        $this->notificationId = $notificationId;
        parent::__construct("Notification [{$notificationId}] not found", $code, $previous);
    }

    public function getNotificationId(): string
    {
        return $this->notificationId;
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'notification_not_found',
            'message' => $this->getMessage(),
            'notification_id' => $this->notificationId,
        ], 404);
    }

    public function report(): bool
    {
        return false;
    }
}
