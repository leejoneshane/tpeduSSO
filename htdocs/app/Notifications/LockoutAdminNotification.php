<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class LockoutAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;
    private $idno;
    private $username;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        self::$idno = $request->get('idno');
        self::$username = $request->get('username');
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
                    ->line('系統接收到使用者 '.self::$username.'(身分證字號'.self::$idno.') 的鎖定帳號事件。')
                    ->line('帳號鎖定將在'.Carbon::now()->addHour().'以後解除！')
                    ->line('系統已經發送電子郵件告知當事人，請留意後續處理情形。');
    }
}
