<?php

namespace App\Domain\Notifications\Services;

use App\Models\ProjectActivity;
use App\Notifications\ProjectActivityNotification;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    public function notifyProjectEvent(ProjectActivity $activity): void
    {
        $project = $activity->project()->withTrashed()->with(['owner', 'members.user'])->first();

        if ($project === null) {
            return;
        }

        $recipients = $project->allMemberUsers()
            ->reject(fn ($user) => $activity->actor_id !== null && $user->is($activity->actor))
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new ProjectActivityNotification($activity));
    }
}
