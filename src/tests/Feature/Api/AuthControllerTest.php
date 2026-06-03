<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_success(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'is_active',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'test@example.com',
                        'is_active' => true,
                    ],
                ],
            ]);
    }

    public function test_login_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['login']);
    }

    public function test_login_inactive_user(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => 'inactive@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'User is blocked.',
            ]);
    }

    public function test_register_success(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Иван Иванов',
            'email' => 'ivan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+79991234567',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'is_active',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'User registered successfully.',
                'data' => [
                    'user' => [
                        'name' => 'Иван Иванов',
                        'email' => 'ivan@example.com',
                        'phone' => '+79991234567',
                        'is_active' => true,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'ivan@example.com',
            'name' => 'Иван Иванов',
        ]);
    }

    public function test_register_validation_error(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_me_success(): void
    {
        $user = User::factory()->create([
            'email' => 'me@example.com',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'email' => 'me@example.com',
                ],
            ]);
    }

    public function test_login_by_phone_success(): void
    {
        $user = User::factory()->create([
            'email' => 'phone@example.com',
            'phone' => '+79991234567',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => '+7 (999) 123-45-67',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'phone@example.com',
                        'phone' => '+79991234567',
                    ],
                ],
            ]);
    }

    public function test_login_by_phone_with_eight_prefix(): void
    {
        $user = User::factory()->create([
            'email' => 'phone8@example.com',
            'phone' => '+79991234567',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => '8 (999) 123-45-67',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'phone8@example.com',
                        'phone' => '+79991234567',
                    ],
                ],
            ]);
    }

    public function test_register_phone_normalization(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Петр Петров',
            'email' => 'petr@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '8 999 123 45 67',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'phone' => '+79991234567',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'petr@example.com',
            'phone' => '+79991234567',
        ]);
    }

    public function test_register_duplicate_phone_validation_error(): void
    {
        User::factory()->create([
            'email' => 'first@example.com',
            'phone' => '+79991234567',
        ]);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Второй',
            'email' => 'second@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+7 (999) 123-45-67',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_me_unauthorized(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    public function test_logout_success(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
