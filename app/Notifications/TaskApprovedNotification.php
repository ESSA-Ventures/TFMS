<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Notifications\Messages\MailMessage;

class TaskApprovedNotification extends BaseNotification
{
    private $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
        $this->company = $this->task->company;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $build = parent::build();
        $url = route('tasks.show', $this->task->id);
        $url = getDomainSpecificUrl($url, $this->company);

        return $build
            ->subject('Task Approved: #' . $this->task->task_short_code)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The task you created has been approved by PSM/Admin.')
            ->line('Task Heading: ' . $this->task->heading)
            ->action('View Task', $url);
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
