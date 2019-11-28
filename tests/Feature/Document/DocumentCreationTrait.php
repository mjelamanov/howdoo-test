<?php

namespace Tests\Feature\Document;

use App\Document;
use App\Enums\DocumentStatus;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Trait DocumentCreationTrait
 *
 * @author Mirlan Jelamanov <mirlan.jelamanov@gmail.com>
 */
trait DocumentCreationTrait
{
    /**
     * @param int|null $userId
     * @param array $payload
     *
     * @return \App\Document
     */
    protected function createDraftDocument(?int $userId = null, array $payload = []): Document
    {
        return $this->createDocument(DocumentStatus::DRAFT(), $userId, $payload);
    }

    /**
     * @param int|null $userId
     * @param array $payload
     *
     * @return \App\Document
     */
    protected function createPublishedDocument(?int $userId = null, array $payload = []): Document
    {
        return $this->createDocument(DocumentStatus::PUBLISHED(), $userId, $payload);
    }

    /**
     * @param \App\Enums\DocumentStatus $status
     * @param int|null $userId
     * @param array $payload
     *
     * @return \App\Document
     */
    protected function createDocument(?DocumentStatus $status = null, ?int $userId = null, array $payload = []): Document
    {
        $factory = factory(Document::class);

        if ($status) {
            $factory = $factory->state($status->getValue());
        }

        return $factory->create(array_filter(['user_id' => $userId, 'payload' => $payload]));
    }

    /**
     * @param int $count
     * @param int|null $userId
     * @param callable|DocumentStatus $status
     *
     * @return \Illuminate\Support\Collection<\App\Document>
     */
    protected function createDocuments(int $count, ?int $userId = null, $status = null): Collection
    {
        if ($status instanceof DocumentStatus) {
            return Collection::times($count, function () use ($userId, $status) {
                return $this->createDocument($status, $userId);
            });
        }

        if (is_callable($status)) {
            return Collection::times($count, function (int $i) use ($count, $userId, $status) {
                return $this->createDocument(call_user_func($status, $i, $count), $userId);
            });
        }

        if (!is_null($status)) {
            throw new InvalidArgumentException(sprintf(
                'Status can only be an instance of %s or a callable that returns a %s instance',
                DocumentStatus::class,
                DocumentStatus::class
            ));
        }

        return factory(Document::class, $count)->create();
    }
}
