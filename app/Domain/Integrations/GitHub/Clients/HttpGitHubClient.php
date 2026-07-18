<?php

namespace App\Domain\Integrations\GitHub\Clients;

use App\Domain\Integrations\GitHub\Exceptions\GitHubApiException;
use App\Domain\Integrations\GitHub\Exceptions\GitHubRepositoryNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class HttpGitHubClient implements GitHubClientInterface
{
    public function fetchRepository(string $owner, string $repo): array
    {
        $response = $this->get("repos/{$owner}/{$repo}");
        $data = $this->assertSuccessful($response, $owner, $repo);

        return [
            'description' => $data['description'] ?? null,
            'stars' => (int) ($data['stargazers_count'] ?? 0),
            'forks' => (int) ($data['forks_count'] ?? 0),
            'open_issues' => (int) ($data['open_issues_count'] ?? 0),
            'language' => $data['language'] ?? null,
            'default_branch' => $data['default_branch'] ?? 'main',
            'html_url' => $data['html_url'] ?? "https://github.com/{$owner}/{$repo}",
            'visibility' => $data['visibility'] ?? ($data['private'] ?? false ? 'private' : 'public'),
            'pushed_at' => $data['pushed_at'] ?? null,
        ];
    }

    public function fetchBranches(string $owner, string $repo, int $limit = 20): array
    {
        $response = $this->get("repos/{$owner}/{$repo}/branches", ['per_page' => $limit]);
        $data = $this->assertSuccessful($response, $owner, $repo);

        return collect($data)->pluck('name')->values()->all();
    }

    public function fetchCommits(string $owner, string $repo, string $branch, int $limit = 10): array
    {
        $response = $this->get("repos/{$owner}/{$repo}/commits", ['sha' => $branch, 'per_page' => $limit]);
        $data = $this->assertSuccessful($response, $owner, $repo);

        return collect($data)->map(fn (array $commit): array => [
            'sha' => mb_substr($commit['sha'] ?? '', 0, 7),
            'message' => explode("\n", $commit['commit']['message'] ?? '')[0],
            'author' => $commit['commit']['author']['name'] ?? ($commit['author']['login'] ?? 'inconnu'),
            'date' => $commit['commit']['author']['date'] ?? null,
        ])->values()->all();
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private function get(string $path, array $query = []): Response
    {
        try {
            return $this->request()->get($path, $query);
        } catch (ConnectionException $e) {
            throw new GitHubApiException("Impossible de joindre l'API GitHub : {$e->getMessage()}");
        }
    }

    private function request(): PendingRequest
    {
        $request = Http::baseUrl(rtrim(config('services.github.base_url'), '/').'/')
            ->timeout(5)
            ->acceptJson()
            ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28']);

        $token = config('services.github.token');

        return $token ? $request->withToken($token) : $request;
    }

    /**
     * @return array<string, mixed>
     */
    private function assertSuccessful(Response $response, string $owner, string $repo): array
    {
        if ($response->status() === 404) {
            throw new GitHubRepositoryNotFoundException("Le dépôt {$owner}/{$repo} est introuvable ou privé.");
        }

        if ($response->failed()) {
            throw new GitHubApiException("L'API GitHub a répondu avec le statut {$response->status()}.");
        }

        return $response->json() ?? [];
    }
}
