<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplateNotFoundException extends Exception
{
    private string $templateId;
    
    public function __construct(string $templateId,int $code = 0, ?\Throwable $previous = null) {
        $this->templateId = $templateId;
        parent::__construct("Template [{$templateId}] not found",$code,$previous);
    }

    public function getTemplateId(): string{
        return $this->templateId;
    }
    
    public function render(Request $request): JsonResponse {
        return response()->json([
            'error'=>'template_not_found',
            'message'=>$this->getMessage(),
            'template_id'=>$this->templateId
        ],404);
    }
    
    public function report():bool{
        return false;
    }
}
