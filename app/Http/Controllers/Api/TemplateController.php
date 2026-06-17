<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveTemplateRequest;
use App\Http\Resources\SaveTemplateResource;
use App\Models\Template;
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
{
    private TemplateService $templateService;
    
    public function __construct(TemplateService $template_service) {
        $this->templateService = $template_service;
    }
    
    public function saveTemplate(SaveTemplateRequest $request){
        $validated = $request->Validated();
        
        $template = $this->templateService->createTemplate($validated);
        
        return new SaveTemplateResource($template);
    }
}