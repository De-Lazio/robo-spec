<?php

namespace App\Notifications;

use App\Models\OrganizationInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class OrganizationInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly OrganizationInvitation $invitation,
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
        $organization = $this->invitation->organization;

        return (new MailMessage)
            ->subject("Invitation à rejoindre {$organization->name} sur RoboForge")
            ->greeting('Bonjour,')
            ->line("Vous avez été invité(e) à rejoindre l'organisation « {$organization->name} » sur RoboForge en tant que {$this->roleLabel()}.")
            ->action('Accepter l\'invitation', route('organization-invitations.accept', ['token' => $this->plainToken]))
            ->line("Cette invitation expire le {$this->invitation->expires_at->translatedFormat('d/m/Y')}.");
    }

    private function roleLabel(): string
    {
        return match ($this->invitation->role->value) {
            'owner' => 'propriétaire',
            'admin' => 'administrateur',
            'member' => 'membre',
            default => $this->invitation->role->value,
        };
    }
}
