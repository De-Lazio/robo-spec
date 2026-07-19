<?php

namespace App\Domain\Integrations\GitHub\Clients;

interface GitHubClientInterface
{
    /**
     * @return array{description: ?string, stars: int, forks: int, open_issues: int, language: ?string, default_branch: string, html_url: string, visibility: string, pushed_at: ?string}
     */
    public function fetchRepository(string $owner, string $repo): array;

    /**
     * @return list<string>
     */
    public function fetchBranches(string $owner, string $repo, int $limit = 20): array;

    /**
     * @return list<array{sha: string, message: string, author: string, date: ?string}>
     */
    public function fetchCommits(string $owner, string $repo, string $branch, int $limit = 10): array;
}
