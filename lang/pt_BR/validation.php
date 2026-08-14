<?php

return [
    'required' => 'O campo :attribute é obrigatório.',
    'email' => 'O campo :attribute deve ser um e-mail válido.',
    'url' => 'O campo :attribute deve ser uma URL válida.',
    'confirmed' => 'A confirmação de :attribute não corresponde.',
    'unique' => 'O campo :attribute já está em uso.',
    'min' => [
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],
    'max' => [
        'file' => 'O campo :attribute não pode ter mais de :max kilobytes.',
        'string' => 'O campo :attribute não pode ter mais de :max caracteres.',
    ],
    'mimes' => 'O campo :attribute deve ser um arquivo do tipo: :values.',
    'repository_url_url' => 'A URL do repositório deve ser uma URL válida.',
    'env_file_mimes' => 'O arquivo deve ser no formato .env ou .txt.',
    'env_file_max' => 'O arquivo .env não pode ter mais de 1MB.',
    'project_file_mimes' => 'O arquivo deve ser no formato .zip.',
    'project_file_max' => 'O arquivo não pode ter mais de 100MB.',
    'attributes' => [
        'email' => 'e-mail',
        'name' => 'nome',
        'password' => 'senha',
        'repository_url' => 'URL do repositório',
        'env_file' => 'arquivo .env',
        'project_file' => 'arquivo do projeto',
        'token' => 'token',
    ],
];
