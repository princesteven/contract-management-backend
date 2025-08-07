<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_username_and_password()
    {
        // Create a test user with a username
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Make a login request
        $response = $this->postJson('/api/login', [
            'username' => 'testuser',
            'password' => 'password',
        ]);

        // Assert the response structure
        $response->assertStatus(200)
            ->assertJsonStructure([
                'username',
                'email',
                'accessToken',
                'refreshToken',
            ]);

        // Assert the username and email match
        $this->assertEquals('testuser', $response->json('username'));
        $this->assertEquals('test@example.com', $response->json('email'));
    }
}
