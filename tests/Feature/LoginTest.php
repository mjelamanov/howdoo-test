<?php

namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class LoginTest extends TestCase
{
    const URI = '/api/v1/login';

    use DatabaseMigrations;

    /**
     * @return void
     */
    public function testLoginShouldReturnClientErrorWhenUserIsNotExist(): void
    {
        $response = $this->postJson(static::URI, [
            'email' => 'non-existen'
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @return void
     */
    public function testLoginAlwaysReturnsNewToken(): void
    {
        $user = factory(User::class)->create();

        $response = $this->postJson(static::URI, [
            'email' => $user->email,
        ]);

        $response->assertOk();

        $email = $response->json('email');
        $until = Carbon::parse($response->json('until'));
        $prevToken = $response->json('token');

        $expectedUntil = Carbon::now()->addMinutes(config('auth.api_token_expire'));

        $this->assertEquals($user->email, $email);
        $this->assertNotNull($prevToken);
        $this->assertTrue($expectedUntil->greaterThanOrEqualTo($until));

        $response = $this->postJson(static::URI, [
            'email' => $user->email,
        ]);

        $response->assertOk();

        $newToken = $response->json('token');

        $this->assertNotNull($newToken);
        $this->assertNotEquals($prevToken, $newToken);
    }
}
