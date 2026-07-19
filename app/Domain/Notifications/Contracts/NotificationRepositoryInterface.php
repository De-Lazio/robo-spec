<?php

namespace App\Domain\Notifications\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

interface NotificationRepositoryInterface
{
    public function paginateForUser(User $user, int $perPage = 20): LengthAwarePaginator;

    /**
     * @return Collection<int, DatabaseNotification>
     */
    public function recentForUser(User $user, int $limit = 8): Collection;

    public function unreadCountForUser(User $user): int;

    public function markAsRead(User $user, string $notificationId): bool;

    public function markAllAsRead(User $user): void;
}
