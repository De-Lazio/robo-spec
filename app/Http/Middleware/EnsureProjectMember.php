<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProjectMember
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $project = $request->route('project');

        if (! $project instanceof Project || ! $request->user()?->can('view', $project)) {
            abort(403);
        }

        return $next($request);
    }
}
