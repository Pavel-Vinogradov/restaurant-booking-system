<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\Restaurant\Enums\StaffRole;
use App\Domain\Restaurant\Models\Restaurant;
use App\Domain\Restaurant\Models\RestaurantStaff;
use App\Domain\Restaurant\Models\Table;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableControllerTest extends TestCase
{
    use RefreshDatabase;

    private Restaurant $restaurant;

    private Table $table;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create();
        $this->table = Table::factory()->create(['restaurant_id' => $this->restaurant->id]);

        // Создаем пользователя и делаем его владельцем ресторана
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
        RestaurantStaff::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'user_id' => $this->user->id,
            'role' => StaffRole::Owner->value,
        ]);
    }

    public function test_can_list_restaurant_tables(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/restaurants/{$this->restaurant->id}/tables");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'restaurant_id',
                        'number',
                        'capacity',
                        'location',
                        'is_available',
                        'description',
                        'min_order_amount',
                        'features',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_can_list_tables_with_pagination(): void
    {
        // Создаем еще столов для пагинации
        Table::factory()->count(15)->create(['restaurant_id' => $this->restaurant->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/restaurants/{$this->restaurant->id}/tables?page=1&per_page=5");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'items' => [
                        '*' => [
                            'id',
                            'number',
                            'capacity',
                        ],
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                    ],
                ],
            ]);

        $this->assertEquals(5, count($response->json('data.items')));
        $this->assertEquals(16, $response->json('data.pagination.total'));
    }

    public function test_can_list_available_tables_only(): void
    {
        // Создаем недоступный стол
        Table::factory()->unavailable()->create(['restaurant_id' => $this->restaurant->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/restaurants/{$this->restaurant->id}/tables?available_only=1");

        $response->assertStatus(200);

        $tables = $response->json('data');
        foreach ($tables as $table) {
            $this->assertTrue($table['is_available']);
        }
    }

    public function test_can_list_tables_by_capacity(): void
    {
        // Создаем столы с разной вместимостью
        Table::factory()->capacity(4)->create(['restaurant_id' => $this->restaurant->id]);
        Table::factory()->capacity(6)->create(['restaurant_id' => $this->restaurant->id]);
        Table::factory()->capacity(8)->create(['restaurant_id' => $this->restaurant->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/restaurants/{$this->restaurant->id}/tables?min_capacity=5&max_capacity=7");

        $response->assertStatus(200);

        $tables = $response->json('data');
        foreach ($tables as $table) {
            $this->assertGreaterThanOrEqual(5, $table['capacity']);
            $this->assertLessThanOrEqual(7, $table['capacity']);
        }
    }

    public function test_cannot_list_tables_without_access(): void
    {
        $otherRestaurant = Restaurant::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/restaurants/{$otherRestaurant->id}/tables");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Доступ запрещен.',
            ]);
    }

    public function test_can_create_table(): void
    {
        $tableData = [
            'restaurant_id' => $this->restaurant->id,
            'number' => 'T15',
            'capacity' => 6,
            'location' => 'VIP-зал',
            'is_available' => true,
            'description' => 'Удобный стол у окна',
            'min_order_amount' => 5000.00,
            'features' => ['window', 'vip'],
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/tables', $tableData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'restaurant_id',
                    'number',
                    'capacity',
                    'location',
                    'is_available',
                    'description',
                    'min_order_amount',
                    'features',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('tables', [
            'restaurant_id' => $this->restaurant->id,
            'number' => 'T15',
            'capacity' => 6,
        ]);
    }

    public function test_cannot_create_table_with_duplicate_number(): void
    {
        $tableData = [
            'restaurant_id' => $this->restaurant->id,
            'number' => $this->table->number, // Дубликат номера
            'capacity' => 4,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/tables', $tableData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => "Стол с номером {$this->table->number} уже существует в этом ресторане.",
            ]);
    }

    public function test_cannot_create_table_without_access(): void
    {
        $otherRestaurant = Restaurant::factory()->create();

        $tableData = [
            'restaurant_id' => $otherRestaurant->id,
            'number' => 'T20',
            'capacity' => 4,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/tables', $tableData);

        $response->assertStatus(403);
    }

    public function test_can_show_table(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/tables/{$this->table->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'restaurant_id',
                    'number',
                    'capacity',
                    'location',
                    'is_available',
                    'description',
                    'min_order_amount',
                    'features',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertEquals($this->table->id, $response->json('data.id'));
    }

    public function test_cannot_show_table_without_access(): void
    {
        $otherRestaurant = Restaurant::factory()->create();
        $otherTable = Table::factory()->create(['restaurant_id' => $otherRestaurant->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/tables/{$otherTable->id}");

        $response->assertStatus(403);
    }

    public function test_can_update_table(): void
    {
        $updateData = [
            'number' => 'T99',
            'capacity' => 8,
            'location' => 'Обновленный зал',
            'is_available' => false,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/tables/{$this->table->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'number',
                    'capacity',
                    'location',
                    'is_available',
                ],
            ]);

        $this->assertEquals('T99', $response->json('data.number'));
        $this->assertEquals(8, $response->json('data.capacity'));
        $this->assertFalse($response->json('data.is_available'));

        $this->assertDatabaseHas('tables', [
            'id' => $this->table->id,
            'number' => 'T99',
            'capacity' => 8,
            'is_available' => false,
        ]);
    }

    public function test_cannot_update_table_without_access(): void
    {
        $otherRestaurant = Restaurant::factory()->create();
        $otherTable = Table::factory()->create(['restaurant_id' => $otherRestaurant->id]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/tables/{$otherTable->id}", ['capacity' => 10]);

        $response->assertStatus(403);
    }

    public function test_can_delete_table(): void
    {
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/tables/{$this->table->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Стол успешно удалён.',
            ]);

        $this->assertDatabaseMissing('tables', ['id' => $this->table->id]);
    }

    public function test_cannot_delete_table_without_access(): void
    {
        $otherRestaurant = Restaurant::factory()->create();
        $otherTable = Table::factory()->create(['restaurant_id' => $otherRestaurant->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/tables/{$otherTable->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('tables', ['id' => $otherTable->id]);
    }

    public function test_table_validation(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/tables', [
                'restaurant_id' => $this->restaurant->id,
                'number' => '',
                'capacity' => -1,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['number', 'capacity']);
    }

    private function createUser()
    {
        return User::factory()->create();
    }
}
