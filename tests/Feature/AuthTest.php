<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_operator_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'operator1@farmersmarket.ci',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['user', 'token'],
                 ])
                 ->assertJson(['success' => true]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'operator1@farmersmarket.ci',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['success' => false]);
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user  = User::where('email', 'operator1@farmersmarket.ci')->first();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/me');

        $response->assertStatus(200)
                 ->assertJsonPath('data.email', 'operator1@farmersmarket.ci');
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/me');
        $response->assertStatus(401);
    }

    public function test_user_can_logout(): void
    {
        $user  = User::where('email', 'operator1@farmersmarket.ci')->first();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/logout');
        $response->assertStatus(200);

        // Le token ne doit plus fonctionner
        $this->withToken($token)->getJson('/api/me')->assertStatus(401);
    }
}