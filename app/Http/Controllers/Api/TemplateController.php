<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveTemplateRequest;
use App\Http\Requests\UpdateTemplateRequest;
use App\Http\Resources\SaveTemplateResource;
use App\Http\Resources\TemplateResource;
use App\Http\Resources\UpdateTemplateResource;
use App\Models\Template;
use App\Services\TemplateService;

class TemplateController extends Controller
{
    private TemplateService $templateService;

    public function __construct(TemplateService $template_service)
    {
        $this->templateService = $template_service;
    }

    public function saveTemplate(SaveTemplateRequest $request)
    {
        $validated = $request->validated();

        $template = $this->templateService->createTemplate($validated);

        return new SaveTemplateResource($template);
    }

    public function listTemplates()
    {
        return TemplateResource::collection($this->templateService->listTemplates());
    }

    public function showTemplate(string $id)
    {
        $template = $this->templateService->getTemplate($id);

        return new TemplateResource($template);
    }

    public function updateTemplate(UpdateTemplateRequest $request, Template $template)
    {
        $validated = $request->validated();

        $template = $this->templateService->updateTemplate($validated, $template);

        return new UpdateTemplateResource($template);
    }

    public function deleteTemplate(Template $template)
    {
        $this->templateService->deleteTemplate($template);

        return response()->json([
            'status' => 'success',
            'message' => 'Template deleted Successfully',
        ]);
    }
}
