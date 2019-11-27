<?php

namespace Tests\Feature;

use App\Document;
use App\Enums\DocumentStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Tests\TestCase;

/**
 * Class DocumentTest
 *
 * @author Mirlan Jelamanov <mirlan.jelamanov@gmail.com>
 */
class DocumentTest extends TestCase
{
    use DatabaseMigrations;

    const BASE_URI = '/api/v1/document';
    const NOT_EXISTING_ID = 100;

    /**
     * @return void
     */
    public function testCreateDocument(): void
    {
        $response = $this->postJson(static::BASE_URI);

        $response->assertCreated();
        $response->assertJson([
            'document' => [
                'id' => 1,
                'status' => DocumentStatus::DRAFT()->getValue(),
                'payload' => [],
                'created_at' => Carbon::now()->toAtomString(),
                'updated_at' => Carbon::now()->toAtomString(),
            ],
        ]);
    }

    /**
     * @return void
     */
    public function testEditDocumentFirstTime(): void
    {
        $document = $this->createDraftDocument();

        $createdAt = $document->getAttribute($document->getCreatedAtColumn());
        $uri = static::BASE_URI . '/' . $document->getKey();
        $payload = [
            'actor' => 'The fox',
            'meta' => [
                'type' => 'quick',
                'color' => 'brown',
            ],
            'actions' => [
                [
                    'action' => 'jump over',
                    'actor' => 'lazy dog',
                ],
            ],
        ];

        $response = $this->putJson($uri, [
            'document' => compact('payload'),
        ]);

        $response->assertSuccessful();
        $response->assertJson([
            'document' => [
                'id' => $document->getKey(),
                'status' => DocumentStatus::DRAFT()->getValue(),
                'payload' => $payload,
                'created_at' => $createdAt->toAtomString(),
                'updated_at' => Carbon::now()->toAtomString(),
            ],
        ]);
    }

    /**
     * @return void
     */
    public function testEditDocument(): void
    {
        $document = $this->createDraftDocument();

        $createdAt = $document->getAttribute($document->getCreatedAtColumn());
        $uri = static::BASE_URI . '/' . $document->getKey();

        $response = $this->putJson($uri, [
            'document' => [
                'payload' => [
                    'meta' => [
                        'type' => 'cunning',
                        'color' => null
                    ],
                    'actions' => [
                        [
                            'action' => 'eat',
                            'actor' => 'blob'
                        ],
                        [
                            'action' => 'run away'
                        ]
                    ]
                ]
            ]
        ]);

        $response->assertSuccessful();
        $response->assertJson([
            'document' => [
                'id' => $document->getKey(),
                'status' => DocumentStatus::DRAFT()->getValue(),
                'payload' => [
                    'meta' => [
                        'type' => 'cunning',
                    ],
                    'actions' => [
                        [
                            'action' => 'eat',
                            'actor' => 'blob'
                        ],
                        [
                            'action' => 'run away'
                        ]
                    ]
                ],
                'created_at' => $createdAt->toAtomString(),
                'updated_at' => Carbon::now()->toAtomString(),
            ],
        ]);
    }

    /**
     * @return void
     */
    public function testTryToPublishEmptyDocument(): void
    {
        $document = $this->createDraftDocument();
        $uri = sprintf('%s/%d/publish', static::BASE_URI, $document->getKey());

        $response = $this->postJson($uri);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return void
     */
    public function testPublishDocument(): void
    {
        $document = $this->createDraftDocument(['actor' => 'The fox']);
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
        $document = $this->createPublishedDocument(['actor' => 'The fox']);
        $uri = sprintf('%s/%d/publish', static::BASE_URI, $document->getKey());

        $response = $this->postJson($uri);

        $response->assertSuccessful();
        $response->assertJson(['document' => $document->toArray()]);
    }

    /**
     * @return void
     */
    public function testUpdatePublishedDocument(): void
    {
        $document = $this->createPublishedDocument(['actor' => 'The fox']);
        $uri = sprintf('%s/%d', static::BASE_URI, $document->getKey());

        $response = $this->putJson($uri, [
            'document' => [
                'payload' => [
                    'meta' => [
                        'type' => 'new',
                    ],
                    'actions' => [
                        [
                            'action' => 'eat',
                            'actor' => 'blob'
                        ],
                        [
                            'action' => 'run away'
                        ]
                    ]
                ]
            ]
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param array $payload
     *
     * @return void
     *
     * @dataProvider emptyPayloadsProvider
     */
    public function testUpdateDocumentWithEmptyPayload(array $payload): void
    {
        $document = $this->createDraftDocument();
        $uri = sprintf('%s/%d', static::BASE_URI, $document->getKey());

        $response = $this->putJson($uri, $payload);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['document.payload']);
    }

    /**
     * @return void
     */
    public function testFetchDocument(): void
    {
        $document = $this->createPublishedDocument(['actor' => 'The fox']);
        $uri = sprintf('%s/%d', static::BASE_URI, $document->getKey());

        $response = $this->getJson($uri);

        $response->assertSuccessful();
        $response->assertJson(['document' => $document->toArray()]);
    }

    /**
     * @return void
     */
    public function testFetchDocumentCollection(): void
    {
        /** @var \Illuminate\Support\Collection<\App\Document> $documents */
        $documents = factory(Document::class, 5)->create();
        $documents = $documents->map(function (Document $document) {
            $attributes = $document->toArray();
            $attributes['status'] = (string) $attributes['status'];

            return $attributes;
        });

        $response = $this->getJson(static::BASE_URI);

        $response->assertSuccessful();
        $response->assertJson([
            'document' => $documents->toArray(),
            'pagination' => [
                'page' => 1,
                'perPage' => 10,
                'total' => $documents->count(),
            ],
        ]);
    }

    /**
     * @param string $method
     * @param string $uri
     *
     * @return void
     *
     * @dataProvider notFoundEnpointsProvider
     */
    public function testEndpointsShouldReturnNotFoundErrorWhenNoDocumentFound(string $method, string $uri): void
    {
        $response = $this->json($method, $uri);

        $response->assertNotFound();
    }

    /**
     * @internal
     * @return array
     */
    public function emptyPayloadsProvider(): array
    {
        return [
            ['document' => []],
            ['document' => ['payload' => null]],
            ['document' => ['payload' => []]],
        ];
    }

    /**
     * @return array
     */
    public function notFoundEnpointsProvider(): array
    {
        return [
            ['GET', static::BASE_URI . '/' . static::NOT_EXISTING_ID],
            ['PUT', static::BASE_URI . '/' . static::NOT_EXISTING_ID],
            ['POST', static::BASE_URI . '/' . static::NOT_EXISTING_ID . '/publish'],
        ];
    }

    /**
     * @param array $payload
     *
     * @return \App\Document
     */
    protected function createDraftDocument(array $payload = []): Document
    {
        return factory(Document::class)->state(DocumentStatus::DRAFT()->getValue())->create(compact('payload'));
    }

    /**
     * @param array $payload
     *
     * @return \App\Document
     */
    protected function createPublishedDocument(array $payload = []): Document
    {
        return factory(Document::class)->state(DocumentStatus::PUBLISHED()->getValue())->create(compact('payload'));
    }
}
