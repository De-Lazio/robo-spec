<?php

namespace App\Http\Controllers;

use App\Domain\Integrations\GitHub\Contracts\GithubLinkRepositoryInterface;
use App\Domain\Integrations\GitHub\Services\GitHubIntegrationService;
use App\Http\Requests\LinkGitHubRepositoryRequest;
use App\Models\GithubRepository;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GitHubController extends Controller
{
    public function __construct(
        private readonly GithubLinkRepositoryInterface $links,
        private readonly GitHubIntegrationService $github,
    ) {}

    public function show(Project $project): Response
    {
        $this->authorize('view', $project);

        $link = $this->links->forProject($project->getKey());

        return Inertia::render('Projects/GitHub', [
            'project' => ['id' => $project->id, 'name' => $project->name],
            'repository' => $link ? $this->linkPayload($link) : null,
            'canManage' => request()->user()->can('manageGithub', $project),
        ]);
    }

    public function store(LinkGitHubRepositoryRequest $request, Project $project): RedirectResponse
    {
        $this->github->link($project, $request->user(), $request->validated('url'));

        return back()->with('success', 'Dépôt GitHub lié.');
    }

    public function sync(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('manageGithub', $project);

        $link = $this->links->forProject($project->getKey());
        abort_unless($link !== null, 404);

        $this->github->sync($link, $request->user());

        return back()->with('success', 'Synchronisation lancée.');
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('manageGithub', $project);

        $link = $this->links->forProject($project->getKey());
        abort_unless($link !== null, 404);

        $this->github->unlink($link, $request->user());

        return back()->with('success', 'Dépôt GitHub délié.');
    }

    /**
     * @return array<string, mixed>
     */
    private function linkPayload(GithubRepository $link): array
    {
        return [
            'owner' => $link->owner,
            'repository' => $link->repository,
            'url' => $link->url,
            'default_branch' => $link->default_branch,
            'visibility' => $link->visibility,
            'sync_status' => $link->sync_status->value,
            'last_synced_at' => $link->last_synced_at?->toDateTimeString(),
            'metadata' => [
                'description' => $link->metadata['description'] ?? null,
                'stars' => $link->metadata['stars'] ?? 0,
                'forks' => $link->metadata['forks'] ?? 0,
                'open_issues' => $link->metadata['open_issues'] ?? 0,
                'language' => $link->metadata['language'] ?? null,
                'branches' => $link->metadata['branches'] ?? [],
                'commits' => $link->metadata['commits'] ?? [],
                'error' => $link->metadata['error'] ?? null,
            ],
        ];
    }
}
