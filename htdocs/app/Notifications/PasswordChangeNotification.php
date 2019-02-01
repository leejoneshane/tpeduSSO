<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;
    private $password = '';

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($password)
    {
        self::$password = $password;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
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
                    ->subject('通知')
                    ->line('系統接收到您的變更密碼要求，已經幫您把原來的密碼更改為新密碼：'.self::$password.'。')
                    ->line('如果您未曾透過系統變更密碼，可能您的帳號已經遭到別人冒用，請儘速使用新密碼登入系統並立即變更您的帳號及密碼。')
                    ->line('若您已經無法登入，請聯絡貴校管理員為您回復密碼。')
                    ->line('回復密碼後，您仍應立即登入系統，並修改您的帳號及密碼。');
    }
}
