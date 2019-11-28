<?php

namespace Tests\Feature;

use App\ApiToken;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Feature\Document\AbstractDocumentTest;
use Tests\TestCase;

/**
 * Class TokenExpirationTest
 *
 * @author Mirlan Jelamanov <mirlan.jelamanov@gmail.com>
 */
class TokenExpirationTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @return void
     */
    public function testSendsExpiredTokenShouldReturnUnauthenticatedError(): void
    {
        $apiToken = factory(ApiToken::class)->state('expired')->create();

        $response = $this->postJson(AbstractDocumentTest::BASE_URI, [], [
            'Authorization' => 'Bearer ' . $apiToken->token,
        ]);

        $response->assertUnauthorized();
    }

    /**
     * @return void
     */
    public function testSendsNonExpiredTokenShouldReturnSuccessfulResponse(): void
    {
        $apiToken = factory(ApiToken::class)->state('active')->create();

        $response = $this->postJson(AbstractDocumentTest::BASE_URI, [], [
            'Authorization' => 'Bearer ' . $apiToken->token,
        ]);

        $response->assertSuccessful();
    }
}
