<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;


class RecipientNotFoundException extends Exception
{
    private string $recipientId;
    
    public function __construct(string $recipientId, int $code = 0, ?Throwable $previous = null) {
        $this->recipientId = $recipientId;
        parent::__construct("Recipient [{$recipientId}] not found",$code,$previous);
    }
    
    public function getRecipientId(): string{
        return $this->recipientId;
    }

    public function render(Request $request): JsonResponse {
        return response()->json([
            'error'=>'recipient_not_found',
            'message'=>$this->getMessage(),
            'recipient_id'=>$this->recipientId
            ],404);
    }

    public function report(): bool {
        return false;
    }
}
