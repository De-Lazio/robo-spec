<?php

namespace Tests\Unit\Domain\Integrations\GitHub;

use App\Domain\Integrations\GitHub\Support\GitHubUrlParser;
use Tests\TestCase;

class GitHubUrlParserTest extends TestCase
{
    public function test_it_parses_valid_github_repository_urls(): void
    {
        $this->assertSame(['owner' => 'laravel', 'repository' => 'laravel'], GitHubUrlParser::parse('https://github.com/laravel/laravel'));
        $this->assertSame(['owner' => 'laravel', 'repository' => 'laravel'], GitHubUrlParser::parse('https://github.com/laravel/laravel/'));
        $this->assertSame(['owner' => 'laravel', 'repository' => 'laravel'], GitHubUrlParser::parse('https://github.com/laravel/laravel.git'));
        $this->assertSame(['owner' => 'laravel', 'repository' => 'laravel'], GitHubUrlParser::parse('https://github.com/laravel/laravel/tree/main'));
        $this->assertSame(['owner' => 'octo-cat', 'repository' => 'hello_world.js'], GitHubUrlParser::parse('https://github.com/octo-cat/hello_world.js'));
    }

    public function test_it_rejects_non_github_or_incomplete_urls(): void
    {
        $this->assertNull(GitHubUrlParser::parse('https://gitlab.com/laravel/laravel'));
        $this->assertNull(GitHubUrlParser::parse('https://github.com/laravel'));
        $this->assertNull(GitHubUrlParser::parse('not a url'));
        $this->assertNull(GitHubUrlParser::parse('http://github.com/laravel/laravel'));
        $this->assertNull(GitHubUrlParser::parse(''));
    }
}
