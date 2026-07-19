<?php

namespace App\Http\Controllers;

use App\Domain\Notifications\Contracts\NotificationRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Notifications/Index', [
            'notifications' => $this->notifications->paginateForUser($request->user(), 20)
                ->through(fn (DatabaseNotification $notification): array => $this->payload($notification)),
        ]);
    }

    public function markAsRead(Request $request, string $notification): RedirectResponse
    {
        $this->notifications->markAsRead($request->user(), $notification);

        return back();
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $this->notifications->markAllAsRead($request->user());

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(DatabaseNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'read' => $notification->read_at !== null,
            'created_at' => $notification->created_at->toIso8601String(),
            ...$notification->data,
        ];
    }
}
