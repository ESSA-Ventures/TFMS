<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class TaskApprovalNeeded extends BaseNotification
{
    private $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
        $this->company = $this->task->company;
    }

    public function via($notifiable)
    {
        $via = ['database', 'mail'];
        return $via;
    }

    public function toMail($notifiable): MailMessage
    {
        $build = parent::build();
        $url = route('tasks.show', $this->task->id);
        $url = getDomainSpecificUrl($url, $this->company);

        return $build
            ->subject('New Task Awaiting Approval: #' . $this->task->task_short_code)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new task has been created and is awaiting your approval.')
            ->line('Task Heading: ' . $this->task->heading)
            ->action('View Task', $url)
            ->line('Please accept or reject the task.');
    }

    public function toArray($notifiable)
    {
        return [
            'id' => $this->task->id,
            'created_at' => $this->task->created_at->format('Y-m-d H:i:s'),
            'heading' => $this->task->heading
        ];
    }
}
