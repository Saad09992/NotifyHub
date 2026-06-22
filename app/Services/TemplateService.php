<?php

namespace App\Services;

use App\Exceptions\TemplateNotFoundException;
use App\Models\Template;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    /**
     * @throws TemplateNotFoundException
     */
    public function getTemplate(string $template_id):Template{
        try {
            return Template::findorFail($template_id);
        }catch (ModelNotFoundException $e){
            throw new TemplateNotFoundException($template_id, previous: $e);
        }
    }

    public function compose(string $template, array $data): string
    {
        // 1. Sanitize the incoming data to prevent XSS attacks
        $safeData = array_map(function ($value) {
            return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        }, $data);

        // 2. Find all instances of {{ key }} or {{key}}
        // The regex looks for {{, optional spaces, alphanumeric words, optional spaces, }}
        return preg_replace_callback('/\{\s*([a-zA-Z0-9_]+)\s*\}/', function ($matches) use ($safeData) {
        
            $key = $matches[1]; // This extracts just the word inside the brackets

            // 3. If the key exists in our data, replace it. Otherwise, leave the {{key}} as is.
            return array_key_exists($key, $safeData) ? $safeData[$key] : $matches[0];

        }, $template);
    }
}