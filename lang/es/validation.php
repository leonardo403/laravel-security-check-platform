<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'email' => 'El campo :attribute debe ser un correo electrónico válido.',
    'url' => 'El campo :attribute debe ser una URL válida.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'unique' => 'El campo :attribute ya está en uso.',
    'min' => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'max' => [
        'file' => 'El campo :attribute no debe ser mayor de :max kilobytes.',
        'string' => 'El campo :attribute no debe ser mayor de :max caracteres.',
    ],
    'mimes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'repository_url_url' => 'La URL del repositorio debe ser una URL válida.',
    'env_file_mimes' => 'El archivo debe tener el formato .env o .txt.',
    'env_file_max' => 'El archivo .env no puede superar 1MB.',
    'project_file_mimes' => 'El archivo debe tener el formato .zip.',
    'project_file_max' => 'El archivo no puede superar 100MB.',
    'attributes' => [
        'email' => 'correo electrónico',
        'name' => 'nombre',
        'password' => 'contraseña',
        'repository_url' => 'URL del repositorio',
        'env_file' => 'archivo .env',
        'project_file' => 'archivo del proyecto',
        'token' => 'token',
    ],
];
