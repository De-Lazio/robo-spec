<?php

namespace App\Notifications;

use App\Models\ProjectInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class ProjectInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ProjectInvitation $invitation,
        public readonly string $plainToken,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $project = $this->invitation->project;

        return (new MailMessage)
            ->subject("Invitation à rejoindre {$project->name} sur RoboForge")
            ->greeting('Bonjour,')
            ->line("Vous avez été invité(e) à rejoindre le projet « {$project->name} » sur RoboForge en tant que {$this->roleLabel()}.")
            ->action('Accepter l\'invitation', route('invitations.accept', ['token' => $this->plainToken]))
            ->line("Cette invitation expire le {$this->invitation->expires_at->translatedFormat('d/m/Y')}.");
    }

    private function roleLabel(): string
    {
        return match ($this->invitation->role->value) {
            'manager' => 'manager',
            'mechanical' => 'contributeur mécanique',
            'electronics' => 'contributeur électronique',
            'software' => 'contributeur logiciel',
            'contributor' => 'contributeur',
            'viewer' => 'observateur',
            default => $this->invitation->role->value,
        };
    }
}
