<?php

namespace App\Jobs;

use App\Domain\Integrations\GitHub\Clients\GitHubClientInterface;
use App\Domain\Integrations\GitHub\Contracts\GithubLinkRepositoryInterface;
use App\Domain\Integrations\GitHub\Enums\SyncStatus;
use App\Domain\Integrations\GitHub\Exceptions\GitHubApiException;
use App\Domain\Integrations\GitHub\Exceptions\GitHubRepositoryNotFoundException;
use App\Models\GithubRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncGitHubRepositoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly GithubRepository $repository,
    ) {}

    public function handle(GitHubClientInterface $client, GithubLinkRepositoryInterface $links): void
    {
        try {
            $repo = $client->fetchRepository($this->repository->owner, $this->repository->repository);
            $branches = $client->fetchBranches($this->repository->owner, $this->repository->repository);
            $commits = $client->fetchCommits($this->repository->owner, $this->repository->repository, $repo['default_branch']);
        } catch (GitHubRepositoryNotFoundException|GitHubApiException $e) {
            $links->update($this->repository, [
                'sync_status' => SyncStatus::Failed,
                'metadata' => array_merge($this->repository->metadata ?? [], ['error' => $e->getMessage()]),
            ]);

            return;
        }

        $links->update($this->repository, [
            'default_branch' => $repo['default_branch'],
            'visibility' => $repo['visibility'],
            'last_synced_at' => now(),
            'sync_status' => SyncStatus::Synced,
            'metadata' => [
                'description' => $repo['description'],
                'stars' => $repo['stars'],
                'forks' => $repo['forks'],
                'open_issues' => $repo['open_issues'],
                'language' => $repo['language'],
                'branches' => $branches,
                'commits' => $commits,
                'error' => null,
            ],
        ]);
    }
}
