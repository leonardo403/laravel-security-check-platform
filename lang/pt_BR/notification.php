<?php

return [
    'subject' => 'Redefinição de senha',
    'greeting' => 'Olá!',
    'line1' => 'Você está recebendo este e-mail porque recebemos uma solicitação de redefinição de senha para a sua conta.',
    'action' => 'Redefinir senha',
    'line2' => 'Este link de redefinição expirará em :count minutos.',
    'line3' => 'Se você não solicitou esta alteração, nenhuma ação é necessária.',

    'scan_created_subject' => 'Scan de segurança iniciado',
    'scan_created_greeting' => 'Olá!',
    'scan_created_line1' => 'Seu scan de segurança no repositório :repo foi iniciado.',
    'scan_created_line2' => 'Estamos analisando seu projeto. Você receberá um e-mail quando o scan for concluído.',
    'scan_created_action' => 'Acompanhar progresso',
    'scan_created_line3' => 'O scan pode levar alguns minutos dependendo do tamanho do projeto.',

    'scan_completed_subject' => 'Scan concluído — Score: :score%',
    'scan_completed_greeting' => 'Seu scan foi concluído!',
    'scan_completed_line1' => 'O scan de segurança no repositório :repo foi finalizado.',
    'scan_completed_score' => 'Score de segurança: :score/100.',
    'scan_completed_action' => 'Ver resultado completo',
    'scan_completed_line2' => 'Acesse o painel para ver detalhes de vulnerabilidades e recomendações.',

    'scan_failed_subject' => 'Scan falhou',
    'scan_failed_greeting' => 'O scan falhou',
    'scan_failed_line1' => 'O scan de segurança no repositório :repo encontrou um erro.',
    'scan_failed_generic' => 'Erro desconhecido durante o processamento.',
    'scan_failed_action' => 'Ver detalhes do erro',
    'scan_failed_line2' => 'Verifique os dados do repositório e tente novamente.',
];
