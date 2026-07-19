<?php

namespace App\Domain\Integrations\GitHub\Support;

class GitHubUrlParser
{
    /**
     * @return array{owner: string, repository: string}|null
     */
    public static function parse(string $url): ?array
    {
        $url = trim($url);

        if (! preg_match('#^https://github\.com/([\w.-]+)/([\w.-]+?)(?:\.git)?(?:/.*)?/?$#i', $url, $matches)) {
            return null;
        }

        return [
            'owner' => $matches[1],
            'repository' => $matches[2],
        ];
    }
}
