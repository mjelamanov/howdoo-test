<?php

namespace Tests\Feature\Document;

use App\Criteria\DocumentOrderedByLatestCriteria;
use App\Criteria\OnlyPublishedDocumentsCriteria;
use App\Document;
use App\Enums\DocumentStatus;
use App\Repositories\DocumentRepository;
use Illuminate\Support\Arr;

/**
 * Class ReadingTest
 *
 * @author Mirlan Jelamanov <mirlan.jelamanov@gmail.com>
 */
class ReadingTest extends AbstractDocumentTest
{
    use DocumentCreationTrait;

    /**
     * @return void
     */
    public function testAnonymousUserCanSeeOnlyPublishedDocuments(): void
    {
        /** @var \Illuminate\Support\Collection<\App\Document> $documents */
        $documents = factory(Document::class, config('document.per_page'))->create();
        $expected = $documents->filter(function (Document $document) {
            return $document->status->is(DocumentStatus::PUBLISHED());
        })->count();

        $response = $this->getJson(static::BASE_URI);

        $response->assertOk();

        $this->assertCount($expected, $response->json('document'));
    }

    /**
     * @return void
     */
    public function testAnonymousUserCanReadOnlyPublishedDocument(): void
    {
        $draft = $this->createDraftDocument();
        $published = $this->createPublishedDocument();

        $response = $this->getJson(static::BASE_URI . '/' . $draft->getKey());
        $response->assertForbidden();

        $response = $this->getJson(static::BASE_URI . '/' . $published->getKey());
        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testAuthenticatedUserCanSeeAllHisDocumentsAndSeeOnlyPublishedOthers(): void
    {
        $user = $this->authenticate();

        $others = $this->createDocuments(4, null, function (int $i) {
            return $i % 2 === 0 ? DocumentStatus::PUBLISHED() : DocumentStatus::DRAFT();
        });

        $ours = $this->createDocuments(4, $user->getKey(), function (int $i) {
            return $i % 2 === 0 ? DocumentStatus::PUBLISHED() : DocumentStatus::DRAFT();
        });

        $response = $this->getJson(static::BASE_URI);

        $response->assertOk();

        $expected = $others->filter(function (Document $document) {
            return $document->status->is(DocumentStatus::PUBLISHED());
        })->merge($ours)->pluck('id')->sort()->all();
        $actual = Arr::sort(Arr::pluck($response->json('document'), 'id'));

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return void
     */
    public function testAuthenticatedUserCanAlwaysReadHisDocument(): void
    {
        $user = $this->authenticate();

        $draft = $this->createDraftDocument($user->getKey());
        $published = $this->createPublishedDocument($user->getKey());

        $response = $this->getJson(static::BASE_URI . '/' . $draft->getKey());
        $response->assertOk();

        $response = $this->getJson(static::BASE_URI . '/' . $published->getKey());
        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testAuthenticatedUserCannotReadUnpublishedDocumentFromOthers(): void
    {
        $this->authenticate();

        $draft = $this->createDraftDocument();
        $published = $this->createPublishedDocument();

        $response = $this->getJson(static::BASE_URI . '/' . $draft->getKey());
        $response->assertForbidden();

        $response = $this->getJson(static::BASE_URI . '/' . $published->getKey());
        $response->assertOk();
    }

    /**
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testDocumentsStructureIsMatching(): void
    {
        /** @var \Illuminate\Support\Collection<\App\Document> $documents */
        $this->createDocuments(5);
        $repository = $this->app->make(DocumentRepository::class);

        $documents = $repository->pushCriteria(new OnlyPublishedDocumentsCriteria())
            ->pushCriteria(new DocumentOrderedByLatestCriteria())->paginate(config('document.per_page'));

        $expected = array_map(function (Document $document) {
            $attributes = $document->toArray();
            $attributes['status'] = (string)$attributes['status'];

            return $attributes;
        }, $documents->items());

        $response = $this->getJson(static::BASE_URI);

        $response->assertSuccessful();
        $response->assertJson([
            'document' => $expected,
            'pagination' => [
                'page' => $documents->currentPage(),
                'perPage' => $documents->perPage(),
                'total' => $documents->total(),
            ],
        ]);
    }

    /**
     * @return void
     */
    public function testDocumentStructureIsMatching(): void
    {
        $document = $this->createPublishedDocument();

        $expected = $document->toArray();

        $response = $this->getJson(static::BASE_URI . '/' . $document->getKey());

        $response->assertOk();
        $response->assertJson(['document' => $expected]);
    }

    /**
     * @return void
     */
    public function testSetPerPage(): void
    {
        $this->createDocuments(5, null, DocumentStatus::PUBLISHED());

        $response = $this->getJson(static::BASE_URI . '?perPage=' . 3);

        $response->assertOk();
        $this->assertEquals([
            'page' => 1,
            'perPage' => 3,
            'total' => 5,
        ], $response->json('pagination'));
    }

    /**
     * @return void
     */
    public function testTryToPublishNonExistenDocumentShouldReturnNotFoundError(): void
    {
        $response = $this->getJson(static::BASE_URI . '/' . static::NOT_EXISTING_ID);

        $response->assertNotFound();
    }
}
