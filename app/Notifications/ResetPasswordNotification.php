<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;
    public $accounts;
    public static $toMailCallback;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token, $accounts)
    {
        $this->token = $token;
        $this->accounts = $accounts;
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
	if (static::$toMailCallback) {
	    return call_user_func(static::$toMailCallback, $notifiable, $this->token);
	}

        return (new MailMessage)
                    ->line('因為系統接收到您重設密碼的要求，所以寄這封信件給您！')
                    ->line('您的登入帳號為：'.$this->accounts)
                    ->action('重設密碼', url(config('app.url').route('password.reset', $this->token, false)))
                    ->line('如果要求重設密碼的並非您本人，請直接刪除信件，不要按「重設密碼」。');
    }

    public function toMailUsing($callback)
    {
	static::$toMailCallback = $callback;
    }
}
