<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Project;

class ProjectAllowedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $project;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
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
                    ->subject(Config::get('app.name').'通知')
                    ->line('系統收到介接專案申請表，內容如下：')
                    ->line('申請單位：'.$this->project->organization)
                    ->line('應用平臺名稱：'.$this->project->applicationName)
                    ->line('申請原因：'.$this->project->reason)
                    ->line('應用平臺網址：'.$this->project->website)
                    ->line('授權碼回傳網址為：'.$this->project->redirect)
                    ->line('聯絡人：'.$this->project->connName)
                    ->line('聯絡部門：'.$this->project->connUnit)
                    ->line('聯絡電話：'.$this->project->connTel)
                    ->line('電子郵件：'.$this->project->connEmail)
                    ->line('請管理員登入<a href="https://ldap.tp.edu.tw">局端管理介面</a>進行審核，謝謝您！');
    }
}
