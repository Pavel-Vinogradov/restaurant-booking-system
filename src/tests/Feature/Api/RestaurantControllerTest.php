<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\Restaurant\Models\Restaurant;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    public function test_index_empty_list(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/restaurants');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'items' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => 20,
                        'total' => 0,
                    ],
                ],
            ]);
    }

    public function test_index_with_restaurants(): void
    {
        $restaurant = Restaurant::factory()->create([
            'name' => 'Мой ресторан',
            'address' => 'ул. Тестовая, 1',
        ]);
        $restaurant->staff()->create([
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/restaurants');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'items' => [
                        '*' => [
                            'id',
                            'name',
                            'address',
                            'phone',
                            'timezone',
                            'status',
                            'opening_hours',
                            'staff',
                        ],
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'items' => [
                        [
                            'name' => 'Мой ресторан',
                            'address' => 'ул. Тестовая, 1',
                        ],
                    ],
                    'pagination' => [
                        'current_page' => 1,
                        'total' => 1,
                    ],
                ],
            ]);
    }

    public function test_index_pagination(): void
    {
        // Создаём 25 ресторанов
        for ($i = 1; $i <= 25; $i++) {
            $restaurant = Restaurant::factory()->create(['name' => "Ресторан {$i}"]);
            $restaurant->staff()->create([
                'user_id' => $this->user->id,
                'role' => 'owner',
            ]);
        }

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/restaurants?page=2&per_page=10');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'pagination' => [
                        'current_page' => 2,
                        'per_page' => 10,
                        'total' => 25,
                        'last_page' => 3,
                    ],
                ],
            ]);

        // Проверяем, что на второй странице правильные элементы
        $data = $response->json('data.items');
        $this->assertCount(10, $data);
    }

    public function test_store_success(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/restaurants', [
                'name' => 'Новый ресторан',
                'address' => 'ул. Новая, 10',
                'phone' => '+79991234567',
                'timezone' => 'Europe/Moscow',
                'opening_hours' => [
                    ['day_of_week' => 0, 'open_time' => '09:00', 'close_time' => '22:00', 'is_closed' => false],
                    ['day_of_week' => 1, 'open_time' => '09:00', 'close_time' => '22:00', 'is_closed' => false],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'address',
                    'phone',
                    'timezone',
                    'status',
                    'opening_hours',
                    'staff',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Ресторан успешно создан.',
                'data' => [
                    'name' => 'Новый ресторан',
                    'address' => 'ул. Новая, 10',
                    'phone' => '+79991234567',
                    'timezone' => 'Europe/Moscow',
                    'status' => 'active',
                ],
            ]);

        // Проверяем, что создатель стал owner
        $this->assertDatabaseHas('restaurant_staff', [
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);

        // Проверяем часы работы
        $this->assertDatabaseHas('opening_hours', [
            'day_of_week' => 0,
            'open_time' => '09:00:00',
            'close_time' => '22:00:00',
        ]);
    }

    public function test_store_validation_error(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/restaurants', [
                'name' => '',
                'address' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'address', 'phone']);
    }

    public function test_show_success(): void
    {
        $restaurant = Restaurant::factory()->create([
            'name' => 'Тестовый ресторан',
        ]);
        $restaurant->staff()->create([
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/restaurants/{$restaurant->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $restaurant->id,
                    'name' => 'Тестовый ресторан',
                ],
            ]);
    }

    public function test_show_not_found(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/restaurants/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Ресторан не найден.',
            ]);
    }

    public function test_show_access_denied(): void
    {
        $otherUser = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $restaurant->staff()->create([
            'user_id' => $otherUser->id,
            'role' => 'owner',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/restaurants/{$restaurant->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'У вас нет доступа к этому ресторану.',
            ]);
    }

    public function test_update_success(): void
    {
        $restaurant = Restaurant::factory()->create([
            'name' => 'Старое название',
        ]);
        $restaurant->staff()->create([
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson("/api/restaurants/{$restaurant->id}", [
                'name' => 'Новое название',
                'address' => 'Новый адрес',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ресторан успешно обновлён.',
                'data' => [
                    'id' => $restaurant->id,
                    'name' => 'Новое название',
                    'address' => 'Новый адрес',
                ],
            ]);

        $this->assertDatabaseHas('restaurants', [
            'id' => $restaurant->id,
            'name' => 'Новое название',
        ]);
    }

    public function test_update_not_found(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/restaurants/99999', [
                'name' => 'Новое название',
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Ресторан не найден.',
            ]);
    }

    public function test_destroy_success(): void
    {
        $restaurant = Restaurant::factory()->create();
        $restaurant->staff()->create([
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson("/api/restaurants/{$restaurant->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ресторан успешно удалён.',
            ]);

        $this->assertDatabaseMissing('restaurants', [
            'id' => $restaurant->id,
        ]);
    }

    public function test_destroy_not_found(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson('/api/restaurants/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Ресторан не найден.',
            ]);
    }

    public function test_unauthorized(): void
    {
        $response = $this->getJson('/api/restaurants');

        $response->assertStatus(401);
    }
}
