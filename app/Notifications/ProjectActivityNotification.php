<?php

namespace App\Notifications;

use App\Models\ProjectActivity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class ProjectActivityNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ProjectActivity $activity,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (in_array($this->activity->event, config('roboforge.notifications.email_events'), true)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        $project = $this->activity->project;

        return [
            'project_id' => $project->getKey(),
            'project_name' => $project->name,
            'event' => $this->activity->event,
            'actor_name' => $this->activity->actor?->name,
            'subject_type' => $this->activity->subject_type,
            'properties' => $this->activity->properties,
            'url' => $this->url(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $project = $this->activity->project;
        $version = $this->activity->properties['version'] ?? '';

        return (new MailMessage)
            ->subject("CDC publié — {$project->name}")
            ->greeting('Bonjour,')
            ->line("Le cahier des charges du projet « {$project->name} » vient d'être publié (version {$version}).")
            ->action('Consulter le CDC', $this->url());
    }

    private function url(): string
    {
        $project = $this->activity->project;
        $prefix = explode('.', $this->activity->event)[0];

        return match ($prefix) {
            'requirements' => route('projects.requirements.show', $project),
            'resource' => route('projects.resources.index', $project),
            'technical_choice' => route('projects.technical-choices.index', $project),
            'task' => route('projects.tasks.index', $project),
            'algorithm_diagram' => route('projects.algorithm-diagrams.index', $project),
            'member' => route('projects.members.index', $project),
            'github' => route('projects.github.show', $project),
            default => route('projects.show', $project),
        };
    }
}
