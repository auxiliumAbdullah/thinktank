<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Leave;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class LeaveApplication extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $leave;
    private $emailSetting;

    public function __construct(Leave $leave)
    {
        $this->leave = $leave;
        $this->emailSetting = EmailNotificationSetting::where('slug', 'new-leave-application')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ['database'];

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('email.leave.applied') . ' - ' . config('app.name'))
            ->greeting(__('email.hello') . ' ' . mb_ucwords($notifiable->name) . '!')
            ->line(__('email.leave.applied') . ':- ')
            ->line(__('app.date') . ': ' . $this->leave->leave_date->toDayDateTimeString())
            ->line(__('app.status') . ': ' . mb_ucwords($this->leave->status))
            ->action(__('email.leave.action'), route('leaves.show', $this->leave->id))
            ->line(__('email.thankyouNote'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
//phpcs:ignore
    public function toArray($notifiable)
    {
        return $this->leave->toArray();
    }

}
