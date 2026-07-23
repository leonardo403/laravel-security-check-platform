<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'repository_url' => 'nullable|url',
            'branch' => 'nullable|string|max:255',
            'env_file' => 'nullable|file|mimes:env,txt|max:1024',
            'project_file' => 'nullable|file|mimes:zip|max:102400',
        ];
    }

    public function messages(): array
    {
        return [
            'repository_url.url' => 'A URL do repositório deve ser uma URL válida.',
            'env_file.mimes' => 'O arquivo deve ser no formato .env ou .txt.',
            'env_file.max' => 'O arquivo .env não pode ter mais de 1MB.',
            'project_file.mimes' => 'O arquivo deve ser no formato .zip.',
            'project_file.max' => 'O arquivo não pode ter mais de 100MB.',
        ];
    }
}
