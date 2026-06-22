<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ChannelNotSupportedException extends Exception
{
    private string $channelName;
    
    public function __construct(string $channelName, int $code = 0, ?Throwable $previous = null) {
        $this->channelName=$channelName;
        parent::__construct("Channel [{$channelName}] not supported", $code, $previous);
    }
    
    public function getChannelName(): string{
        return $this->channelName;
    }

    public function render(Request $request): JsonResponse {
        return response()->json([
            'error'=>'channel_not_supported',
            'message'=>$this->getMessage(),
            'channel_name'=>$this->channelName
        ],422);
    }
    
    public function report(): bool{
        return false;
    }
}
