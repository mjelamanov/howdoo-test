<?php

namespace Tests\Feature\Document;

use App\Enums\DocumentStatus;
use Carbon\Carbon;
use Illuminate\Http\Response;

/**
 * Class EditTest
 *
 * @author Mirlan Jelamanov <mirlan.jelamanov@gmail.com>
 */
class EditTest extends AbstractDocumentTest
{
    use DocumentCreationTrait;

    /**
     * @return void
     */
    public function testAnonymousCannotEditDocument(): void
    {
        $document = $this->createDraftDocument();

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

        $response->assertUnauthorized();
    }

    /**
     * @return void
     */
    public function testUserTriesToUpdateAnotherDocument(): void
    {
        $document = $this->createDraftDocument();
        $this->authenticate();

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

        $response->assertForbidden();
    }

    /**
     * @return void
     */
    public function testEditFirstTime(): void
    {
        $user = $this->authenticate();

        $document = $this->createDraftDocument($user->getKey());

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
    public function testEditMoreThanOneTime(): void
    {
        $user = $this->authenticate();

        $document = $this->createDraftDocument($user->getKey());

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
    public function testEditPublishedDocument(): void
    {
        $user = $this->authenticate();

        $document = $this->createPublishedDocument($user->getKey(), ['actor' => 'The fox']);
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
    public function testTryToUpdateWithEmptyPayload(array $payload): void
    {
        $user = $this->authenticate();

        $document = $this->createDraftDocument($user->getKey());

        $uri = sprintf('%s/%d', static::BASE_URI, $document->getKey());

        $response = $this->putJson($uri, $payload);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['document.payload']);
    }

    /**
     * @return void
     */
    public function testTryToUpdateNonExistenDocumentShouldReturnNotFoundError(): void
    {
        $this->authenticate();

        $response = $this->putJson(static::BASE_URI . '/' . static::NOT_EXISTING_ID);

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
}
