<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthFeatureTest extends TestCase
{
    public function test_login_success_returns_token_and_user_payload(): void
    {
        $user = User::factory()->create([
            'email' => 'login-test@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'user',
                'token',
                'roles',
                'permissions',
            ])
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_login_fail_returns_validation_error(): void
    {
        $user = User::factory()->create([
            'email' => 'login-fail@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');

        $response->assertOk()
            ->assertJsonPath('message', '登出成功');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_me_returns_authenticated_user_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email);
    }
}

