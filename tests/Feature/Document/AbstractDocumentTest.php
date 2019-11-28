<?php

namespace Tests\Feature\Document;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

/**
 * Class AbstractDocumentTest
 *
 * @author Mirlan Jelamanov <mirlan.jelamanov@gmail.com>
 */
abstract class AbstractDocumentTest extends TestCase
{
    use DatabaseMigrations;

    const BASE_URI = '/api/v1/document';
    const NOT_EXISTING_ID = 1000;

    /**
     * @return \App\User
     */
    protected function authenticate(): User
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'api');

        return $user;
    }
}
