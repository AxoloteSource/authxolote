<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocsEndpointTest extends TestCase
{
    private string $documentationPath;

    private bool $createdDocumentation = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->documentationPath = public_path('docs/index.html');

        if (! file_exists($this->documentationPath)) {
            @mkdir(dirname($this->documentationPath), 0755, true);
            file_put_contents($this->documentationPath, '<html><body>API documentation</body></html>');
            $this->createdDocumentation = true;
        }
    }

    protected function tearDown(): void
    {
        if ($this->createdDocumentation && file_exists($this->documentationPath)) {
            unlink($this->documentationPath);
            @rmdir(dirname($this->documentationPath));
        }

        parent::tearDown();
    }

    public function test_docs_route_serves_the_generated_documentation(): void
    {
        $response = $this->get('/docs');

        $response->assertOk();

        $this->assertSame(
            realpath($this->documentationPath),
            $response->baseResponse->getFile()->getPathname()
        );
    }

    public function test_docs_route_returns_not_found_when_documentation_was_not_generated(): void
    {
        $this->withExceptionHandling();

        $missingPath = public_path('docs/index.html');
        $backup = file_get_contents($missingPath);

        unlink($missingPath);

        try {
            $this->get('/docs')->assertNotFound();
        } finally {
            file_put_contents($missingPath, $backup);
        }
    }
}
