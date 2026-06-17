<?php

namespace App\Services;

use App\Models\Template;
use Illuminate\Support\Facades\Auth;

class TemplateService
{
    public function createTemplate($data):Template{
        return Template::create([
            'user_id'=>Auth::user()->id,
            'template_body'=>$data['template_body'],
            'supported_channels'=>$data['supported_channels']
        ]);
    }
}