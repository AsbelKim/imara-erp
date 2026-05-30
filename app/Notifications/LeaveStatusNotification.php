<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveStatusNotification extends Notification
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status  = ucfirst($this->leaveRequest->status);
        $type    = $this->leaveRequest->leaveType->name;
        $start   = $this->leaveRequest->start_date->format('d M Y');
        $end     = $this->leaveRequest->end_date->format('d M Y');
        $days    = $this->leaveRequest->days_requested;

        $mail = (new MailMessage)
            ->subject("Leave Request {$status} — Imara Logic ERP")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your **{$type}** leave request for **{$days} day(s)** ({$start} – {$end}) has been **{$status}**.");

        if ($this->leaveRequest->status === 'rejected' && $this->leaveRequest->rejection_reason) {
            $mail->line("**Reason:** {$this->leaveRequest->rejection_reason}");
        }

        return $mail->line('Thank you, Imara Logic HR Team.');
    }
}
