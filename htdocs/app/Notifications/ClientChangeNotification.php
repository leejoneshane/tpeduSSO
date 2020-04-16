<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Project;

class ClientChangeNotification extends Notification implements ShouldQueue
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
                    ->subject(config('app.name').'通知')
                    ->greeting('OAuth 用戶端異動：')
                    ->line('管理員已經為您申請的介接專案：'.$this->project->applicationName.'進行資料變更，新的設定如下：')
                    ->line('OAuth 用戶端代號為：'.$this->project->getClient()->id.'。')
                    ->line('OAuth 用戶端密鑰為：'.$this->project->getClient()->secret.'。')
                    ->line('授權碼回傳網址為：'.$this->project->getClient()->redirect.'。')
                    ->line('此封信件請閱讀後銷毀，以免影響到應用服務的安全性。若有需要，請將上述資料離線保存！');
    }
}
