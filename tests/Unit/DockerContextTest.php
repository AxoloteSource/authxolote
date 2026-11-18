<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class DockerContextTest extends TestCase
{
    public function test_dockerignore_excludes_passport_keys(): void
    {
        $this->assertContains('storage/*.key', $this->lines('.dockerignore'));
    }

    public function test_dockerfile_fails_when_passport_keys_are_present(): void
    {
        $dockerfile = $this->contents('Dockerfile');

        $this->assertStringContainsString('test ! -e storage/oauth-private.key', $dockerfile);
        $this->assertStringContainsString('test ! -e storage/oauth-public.key', $dockerfile);
    }

    private function contents(string $file): string
    {
        return (string) file_get_contents($this->path($file));
    }

    /**
     * @return array<int, string>
     */
    private function lines(string $file): array
    {
        return array_map('trim', file($this->path($file), FILE_IGNORE_NEW_LINES) ?: []);
    }

    private function path(string $file): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.$file;
    }
}
