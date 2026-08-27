<?php

return [
    'subject' => 'Restablecer contraseña',
    'greeting' => '¡Hola!',
    'line1' => 'Recibes este correo porque hemos recibido una solicitud de restablecimiento de contraseña para tu cuenta.',
    'action' => 'Restablecer contraseña',
    'line2' => 'Este enlace de restablecimiento expirará en :count minutos.',
    'line3' => 'Si no solicitaste este cambio, no se requiere ninguna acción.',

    'scan_created_subject' => 'Escaneo de seguridad iniciado',
    'scan_created_greeting' => '¡Hola!',
    'scan_created_line1' => 'Tu escaneo de seguridad en el repositorio :repo ha sido iniciado.',
    'scan_created_line2' => 'Estamos analizando tu proyecto. Recibirás un correo cuando el escaneo finalice.',
    'scan_created_action' => 'Seguir progreso',
    'scan_created_line3' => 'El escaneo puede tardar unos minutos dependiendo del tamaño del proyecto.',

    'scan_completed_subject' => 'Escaneo completado — Puntuación: :score%',
    'scan_completed_greeting' => '¡Tu escaneo ha sido completado!',
    'scan_completed_line1' => 'El escaneo de seguridad en el repositorio :repo ha finalizado.',
    'scan_completed_score' => 'Puntuación de seguridad: :score/100.',
    'scan_completed_action' => 'Ver resultados completos',
    'scan_completed_line2' => 'Accede al panel para ver detalles de vulnerabilidades y recomendaciones.',

    'scan_failed_subject' => 'Escaneo fallido',
    'scan_failed_greeting' => 'El escaneo falló',
    'scan_failed_line1' => 'El escaneo de seguridad en el repositorio :repo encontró un error.',
    'scan_failed_generic' => 'Ocurrió un error desconocido durante el procesamiento.',
    'scan_failed_action' => 'Ver detalles del error',
    'scan_failed_line2' => 'Verifica los datos del repositorio e intenta de nuevo.',
];
