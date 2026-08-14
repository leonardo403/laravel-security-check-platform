<?php

return [
    'required' => 'The :attribute field is required.',
    'email' => 'The :attribute must be a valid email address.',
    'url' => 'The :attribute must be a valid URL.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'unique' => 'The :attribute has already been taken.',
    'min' => [
        'string' => 'The :attribute must be at least :min characters.',
    ],
    'max' => [
        'file' => 'The :attribute may not be greater than :max kilobytes.',
        'string' => 'The :attribute may not be greater than :max characters.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'repository_url_url' => 'The repository URL must be a valid URL.',
    'env_file_mimes' => 'The file must be in .env or .txt format.',
    'env_file_max' => 'The .env file may not be greater than 1MB.',
    'project_file_mimes' => 'The file must be in .zip format.',
    'project_file_max' => 'The file may not be greater than 100MB.',
    'attributes' => [
        'email' => 'email',
        'name' => 'name',
        'password' => 'password',
        'repository_url' => 'repository URL',
        'env_file' => '.env file',
        'project_file' => 'project file',
        'token' => 'token',
    ],
];
