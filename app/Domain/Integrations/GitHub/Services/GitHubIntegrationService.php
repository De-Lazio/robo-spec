<?php

namespace App\Domain\Integrations\GitHub\Services;

use App\Domain\Integrations\GitHub\Clients\GitHubClientInterface;
use App\Domain\Integrations\GitHub\Contracts\GithubLinkRepositoryInterface;
use App\Domain\Integrations\GitHub\Enums\SyncStatus;
use App\Domain\Integrations\GitHub\Exceptions\GitHubApiException;
use App\Domain\Integrations\GitHub\Exceptions\GitHubRepositoryNotFoundException;
use App\Domain\Integrations\GitHub\Support\GitHubUrlParser;
use App\Domain\Notifications\Services\NotificationService;
use App\Domain\Projects\Contracts\ProjectActivityRepositoryInterface;
use App\Jobs\SyncGitHubRepositoryJob;
use App\Models\GithubRepository;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GitHubIntegrationService
{
    public function __construct(
        private readonly GithubLinkRepositoryInterface $links,
        private readonly GitHubClientInterface $client,
        private readonly ProjectActivityRepositoryInterface $activities,
        private readonly NotificationService $notifications,
    ) {}

    public function link(Project $project, User $actor, string $url): GithubRepository
    {
        $parsed = GitHubUrlParser::parse($url);

        if ($parsed === null) {
            throw ValidationException::withMessages([
                'url' => ['Cette URL ne pointe pas vers un dépôt GitHub (format attendu : https://github.com/organisation/depot).'],
            ]);
        }

        if ($this->links->forProject($project->getKey()) !== null) {
            throw ValidationException::withMessages([
                'url' => ['Ce projet a déjà un dépôt GitHub lié. Déliez-le avant d’en lier un autre.'],
            ]);
        }

        ['owner' => $owner, 'repository' => $repository] = $parsed;

        try {
            $repo = $this->client->fetchRepository($owner, $repository);
            $branches = $this->client->fetchBranches($owner, $repository);
            $commits = $this->client->fetchCommits($owner, $repository, $repo['default_branch']);
        } catch (GitHubRepositoryNotFoundException $e) {
            throw ValidationException::withMessages(['url' => [$e->getMessage()]]);
        } catch (GitHubApiException $e) {
            throw ValidationException::withMessages(['url' => ["Impossible de contacter GitHub pour le moment : {$e->getMessage()}"]]);
        }

        $activity = null;

        $link = DB::transaction(function () use ($project, $actor, $owner, $repository, $repo, $branches, $commits, &$activity): GithubRepository {
            $link = $this->links->create([
                'project_id' => $project->getKey(),
                'provider' => 'github',
                'owner' => $owner,
                'repository' => $repository,
                'url' => $repo['html_url'],
                'default_branch' => $repo['default_branch'],
                'visibility' => $repo['visibility'],
                'last_synced_at' => now(),
                'sync_status' => SyncStatus::Synced,
                'metadata' => $this->metadataFrom($repo, $branches, $commits),
            ]);

            $activity = $this->activities->create([
                'project_id' => $project->getKey(),
                'actor_id' => $actor->getKey(),
                'event' => 'github.linked',
                'subject_type' => GithubRepository::class,
                'subject_id' => $link->getKey(),
                'properties' => ['owner' => $owner, 'repository' => $repository],
            ]);

            return $link;
        });

        $this->notifications->notifyProjectEvent($activity);

        return $link;
    }

    public function sync(GithubRepository $link, User $actor): void
    {
        $this->links->update($link, ['sync_status' => SyncStatus::Pending]);

        $activity = $this->activities->create([
            'project_id' => $link->project_id,
            'actor_id' => $actor->getKey(),
            'event' => 'github.sync_requested',
            'subject_type' => GithubRepository::class,
            'subject_id' => $link->getKey(),
            'properties' => [],
        ]);

        $this->notifications->notifyProjectEvent($activity);

        SyncGitHubRepositoryJob::dispatch($link);
    }

    public function unlink(GithubRepository $link, User $actor): void
    {
        $activity = null;

        DB::transaction(function () use ($link, $actor, &$activity): void {
            $activity = $this->activities->create([
                'project_id' => $link->project_id,
                'actor_id' => $actor->getKey(),
                'event' => 'github.unlinked',
                'subject_type' => GithubRepository::class,
                'subject_id' => $link->getKey(),
                'properties' => ['owner' => $link->owner, 'repository' => $link->repository],
            ]);

            $this->links->delete($link);
        });

        $this->notifications->notifyProjectEvent($activity);
    }

    /**
     * @param  array<string, mixed>  $repo
     * @param  list<string>  $branches
     * @param  list<array<string, mixed>>  $commits
     * @return array<string, mixed>
     */
    private function metadataFrom(array $repo, array $branches, array $commits): array
    {
        return [
            'description' => $repo['description'],
            'stars' => $repo['stars'],
            'forks' => $repo['forks'],
            'open_issues' => $repo['open_issues'],
            'language' => $repo['language'],
            'branches' => $branches,
            'commits' => $commits,
            'error' => null,
        ];
    }
}
