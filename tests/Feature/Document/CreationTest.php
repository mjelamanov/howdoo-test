<?php

namespace Tests\Feature\Document;

use App\Document;
use App\Enums\DocumentStatus;
use App\User;
use Carbon\Carbon;

/**
 * Class CreationTest
 *
 * @author Mirlan Jelamanov <mirlan.jelamanov@gmail.com>
 */
class CreationTest extends AbstractDocumentTest
{
    /**
     * @return void
     */
    public function testAnonymousCannotCreateDocument(): void
    {
        $response = $this->postJson(static::BASE_URI);

        $response->assertUnauthorized();
    }

    /**
     * @return void
     */
    public function testAuthenticatedUserCanCreateDocument(): void
    {
        $this->authenticate();

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
    public function testDocumentOwnerIsAuthenticatedUser(): void
    {
        $user = $this->authenticate();

        $response = $this->postJson(static::BASE_URI);
        $response->assertCreated();

        $this->assertDatabaseHas((new Document())->getTable(), [
            'id' => 1,
            $user->getForeignKey() => $user->getKey(),
        ]);
    }
}
