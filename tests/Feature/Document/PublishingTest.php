<?php

namespace Tests\Feature\Document;

use App\Enums\DocumentStatus;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * Class PublishingTest
 *
 * @author Mirlan Jelamanov <mirlan.jelamanov@gmail.com>
 */
class PublishingTest extends AbstractDocumentTest
{
    use DocumentCreationTrait;

    /**
     * @return void
     */
    public function testAnonymousCannotPublishDocument(): void
    {
        $document = $this->createDraftDocument(null, ['actor' => 'The fox']);

        $uri = sprintf('%s/%d/publish', static::BASE_URI, $document->getKey());

        $response = $this->postJson($uri);
        $response->assertUnauthorized();
    }

    /**
     * @return void
     */
    public function testTryToPublishAnotherDocument(): void
    {
        $this->authenticate();

        $document = $this->createDraftDocument(null, ['actor' => 'The fox']);

        $uri = sprintf('%s/%d/publish', static::BASE_URI, $document->getKey());

        $response = $this->postJson($uri);
        $response->assertForbidden();
    }

    /**
     * @return void
     */
    public function testPublishDocument(): void
    {
        $user = $this->authenticate();

        $document = $this->createDraftDocument($user->getKey(), ['actor' => 'The fox']);
        $uri = sprintf('%s/%d/publish', static::BASE_URI, $document->getKey());

        $expected = array_merge(Arr::except($document->toArray(), ['status', 'updated_at']), [
            'status' => DocumentStatus::PUBLISHED()->getValue(),
            'updated_at' => Carbon::now()->toAtomString(),
        ]);

        $response = $this->postJson($uri);

        $response->assertSuccessful();
        $response->assertJson(['document' => $expected]);
    }

    /**
     * @return void
     */
    public function testRepublishDocument(): void
    {
        $user = $this->authenticate();

        $document = $this->createPublishedDocument($user->getKey(), ['actor' => 'The fox']);
        $uri = sprintf('%s/%d/publish', static::BASE_URI, $document->getKey());

        $response = $this->postJson($uri);

        $response->assertSuccessful();
        $response->assertJson(['document' => $document->toArray()]);
    }

    /**
     * @return void
     */
    public function testTryToPublishNonExistenDocumentShouldReturnNotFoundError(): void
    {
        $this->authenticate();

        $response = $this->postJson(static::BASE_URI . '/' . static::NOT_EXISTING_ID . '/publish');

        $response->assertNotFound();
    }
}
