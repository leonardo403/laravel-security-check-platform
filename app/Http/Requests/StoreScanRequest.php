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
            'repository_url.url' => __('validation.repository_url_url'),
            'env_file.mimes' => __('validation.env_file_mimes'),
            'env_file.max' => __('validation.env_file_max'),
            'project_file.mimes' => __('validation.project_file_mimes'),
            'project_file.max' => __('validation.project_file_max'),
        ];
    }
}
