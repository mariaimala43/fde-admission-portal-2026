<?php

namespace App\Notifications;

use App\Mail\DailyAdmissionReminder as DailyAdmissionReminderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyAdmissionReminder extends Notification
{
    use Queueable;

    public string $schoolName;
    public string $cutoffTime;
    public string $date;

    public function __construct(string $schoolName, string $cutoffTime)
    {
        $this->schoolName = $schoolName;
        $this->cutoffTime = $cutoffTime;
        $this->date       = now()->setTimezone('Asia/Karachi')->format('d F Y');
    }

    /**
     * Delivery channels — database (in-app bell) + mail (email reminder).
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->mailable(
            new DailyAdmissionReminderMail($notifiable, $this->schoolName, $this->cutoffTime)
        );
    }

    /**
     * Data stored in the notifications table JSON column.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type'        => 'daily_admission_reminder',
            'title'       => 'Daily Admission Entry Pending',
            'message'     => "You have not submitted today's admission data for {$this->schoolName}. Please submit before {$this->cutoffTime}.",
            'school_name' => $this->schoolName,
            'cutoff_time' => $this->cutoffTime,
            'date'        => $this->date,
            'action_url'  => '/hoi/admissions/daily',
            'action_text' => 'Submit Now',
            'icon'        => 'bell',
            'colour'      => 'amber',
        ];
    }
}
