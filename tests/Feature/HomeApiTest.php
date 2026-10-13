<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Home;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeApiTest extends TestCase
{
    // Fresh empty database for every test
    use RefreshDatabase;

    // Helper: one cheap home in Frisco, one expensive home in Plano
    private function makeHomes(): Community
    {
        $frisco = Community::create(['name' => 'Lakewood', 'city' => 'Frisco']);
        $plano  = Community::create(['name' => 'Oak Hills', 'city' => 'Plano']);

        Home::create(['community_id' => $frisco->id, 'address' => '1 Cheap St',  'price' => 300000, 'bedrooms' => 3]);
        Home::create(['community_id' => $plano->id,  'address' => '2 Pricey St', 'price' => 600000, 'bedrooms' => 5]);

        return $frisco;
    }

    public function test_max_price_filter_returns_only_cheaper_homes(): void
    {
        $this->makeHomes();

        $this->getJson('/api/homes?max_price=400000')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.address', '1 Cheap St');
    }

    public function test_city_filter_returns_only_that_city(): void
    {
        $this->makeHomes();

        $this->getJson('/api/homes?city=Plano')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.address', '2 Pricey St');
    }

    public function test_invalid_max_price_returns_422(): void
    {
        $this->getJson('/api/homes?max_price=abc')
            ->assertStatus(422)
            ->assertJsonValidationErrors('max_price');
    }

    public function test_guest_cannot_create_a_home(): void
    {
        $this->postJson('/api/homes', [])
            ->assertUnauthorized(); // 401
    }

    public function test_logged_in_user_can_create_a_home(): void
    {
        $community = $this->makeHomes();
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/homes', [
                'community_id' => $community->id,
                'address'      => '9 New Rd',
                'price'        => 450000,
                'bedrooms'     => 4,
            ])
            ->assertCreated() // 201
            ->assertJsonPath('data.address', '9 New Rd');

        $this->assertDatabaseHas('homes', ['address' => '9 New Rd']);
    }
}