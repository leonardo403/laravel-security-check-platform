<?php

namespace App\Notifications;

use App\Models\Scan;
use App\Models\ScanResult;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScanNotification extends Notification
{
    use Queueable;

    public const STATUS_CREATED = 'created';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public function __construct(
        public Scan $scan,
        public string $status,
        public ?ScanResult $result = null,
        public ?string $errorMessage = null,
    ) {
        $this->locale = app()->getLocale();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return match ($this->status) {
            self::STATUS_CREATED => $this->createdMail(),
            self::STATUS_COMPLETED => $this->completedMail(),
            self::STATUS_FAILED => $this->failedMail(),
            default => $this->completedMail(),
        };
    }

    private function createdMail(): MailMessage
    {
        $repo = $this->scan->repository_url;

        return (new MailMessage)
            ->subject(__('notification.scan_created_subject'))
            ->greeting(__('notification.scan_created_greeting'))
            ->line(__('notification.scan_created_line1', ['repo' => $repo]))
            ->line(__('notification.scan_created_line2'))
            ->action(__('notification.scan_created_action'), url(route('scans.show', $this->scan)))
            ->line(__('notification.scan_created_line3'));
    }

    private function completedMail(): MailMessage
    {
        $score = $this->result?->score ?? '?';
        $repo = $this->scan->repository_url;

        $mail = (new MailMessage)
            ->subject(__('notification.scan_completed_subject', ['score' => $score]))
            ->greeting(__('notification.scan_completed_greeting'))
            ->line(__('notification.scan_completed_line1', ['repo' => $repo]))
            ->line(__('notification.scan_completed_score', ['score' => $score]))
            ->action(__('notification.scan_completed_action'), url(route('scans.show', $this->scan)));

        if ($this->result && $this->result->summary) {
            $mail->line($this->result->summary);
        }

        $mail->line(__('notification.scan_completed_line2'));

        return $mail;
    }

    private function failedMail(): MailMessage
    {
        $repo = $this->scan->repository_url;

        return (new MailMessage)
            ->subject(__('notification.scan_failed_subject'))
            ->greeting(__('notification.scan_failed_greeting'))
            ->line(__('notification.scan_failed_line1', ['repo' => $repo]))
            ->line($this->errorMessage ?? __('notification.scan_failed_generic'))
            ->action(__('notification.scan_failed_action'), url(route('scans.show', $this->scan)))
            ->line(__('notification.scan_failed_line2'));
    }
}
