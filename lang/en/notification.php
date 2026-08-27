<?php

return [
    'subject' => 'Reset Password Notification',
    'greeting' => 'Hello!',
    'line1' => 'You are receiving this email because we received a password reset request for your account.',
    'action' => 'Reset Password',
    'line2' => 'This password reset link will expire in :count minutes.',
    'line3' => 'If you did not request a password reset, no further action is required.',

    'scan_created_subject' => 'Security scan started',
    'scan_created_greeting' => 'Hello!',
    'scan_created_line1' => 'Your security scan on repository :repo has been started.',
    'scan_created_line2' => 'We are analyzing your project. You will receive an email when the scan is complete.',
    'scan_created_action' => 'Track progress',
    'scan_created_line3' => 'The scan may take a few minutes depending on the project size.',

    'scan_completed_subject' => 'Scan completed — Score: :score%',
    'scan_completed_greeting' => 'Your scan has been completed!',
    'scan_completed_line1' => 'The security scan on repository :repo has finished.',
    'scan_completed_score' => 'Security score: :score/100.',
    'scan_completed_action' => 'View full results',
    'scan_completed_line2' => 'Access the dashboard to see vulnerability details and recommendations.',

    'scan_failed_subject' => 'Scan failed',
    'scan_failed_greeting' => 'The scan failed',
    'scan_failed_line1' => 'The security scan on repository :repo encountered an error.',
    'scan_failed_generic' => 'An unknown error occurred during processing.',
    'scan_failed_action' => 'View error details',
    'scan_failed_line2' => 'Please check your repository data and try again.',
];
