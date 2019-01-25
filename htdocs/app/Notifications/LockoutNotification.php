<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class LockoutNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
    //
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
                    ->error()
                    ->subject('緊急通知')
                    ->line('系統接收到您的登入要求，然而驗證失敗次數太多，所以已經鎖定您的帳號。')
                    ->line('請在'.Carbon::now()->addHour().'以後再嘗試登入！')
                    ->line('如果登入的並非您本人，可能您的帳號已經遭到別人冒用，請儘速登入系統並變更您的密碼。')
                    ->line('若您已經無法登入，請聯絡貴校管理員為您回復密碼。')
                    ->line('回復密碼後，您仍應立即登入系統，並變更您的預設帳號及預設密碼。');
    }
}
