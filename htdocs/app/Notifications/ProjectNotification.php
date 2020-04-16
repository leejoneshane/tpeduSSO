<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Project;

class ProjectNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $project;
    private $header;
    private $messages;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Project $project, array $messages, $header = '')
    {
        if (empty($header)) $header = config('app.name').'通知';
        $this->project = $project;
        $this->header = $header;
        $this->messages = $messages;
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
        $mail = new MailMessage;
        $mail->subject($header)->greeting($project->applicationName.'的管理員，您好！');
        foreach ($this->messages as $line) {
            $mail->line($line);
        }
        return $mail;
    }
}
