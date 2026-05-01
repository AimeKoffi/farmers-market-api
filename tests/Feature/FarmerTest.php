<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Farmer;
use Tests\TestCase;

class FarmerTest extends TestCase
{
    private string $operatorToken;
    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $operator = User::where('role', 'operator')->first();
        $admin    = User::where('role', 'admin')->first();

        $this->operatorToken = $operator->createToken('test')->plainTextToken;
        $this->adminToken    = $admin->createToken('test')->plainTextToken;
    }

    public function test_operator_can_search_farmer_by_identifier(): void
    {
        $response = $this->withToken($this->operatorToken)
            ->getJson('/api/farmers/search?q=AGR-001');

        $response->assertStatus(200)
                 ->assertJsonPath('data.identifier', 'AGR-001');
    }

    public function test_operator_can_search_farmer_by_phone(): void
    {
        $response = $this->withToken($this->operatorToken)
            ->getJson('/api/farmers/search?q=0701010101');

        $response->assertStatus(200)
                 ->assertJsonPath('data.phone', '0701010101');
    }

    public function test_search_returns_404_for_unknown_farmer(): void
    {
        $response = $this->withToken($this->operatorToken)
            ->getJson('/api/farmers/search?q=UNKNOWN');

        $response->assertStatus(404);
    }

    public function test_operator_can_create_farmer(): void
    {
        $response = $this->withToken($this->operatorToken)
            ->postJson('/api/farmers', [
                'identifier'   => 'AGR-NEW',
                'firstname'    => 'Nouveau',
                'lastname'     => 'Agriculteur',
                'phone'        => '0799999999',
                'credit_limit' => 100000,
            ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.identifier', 'AGR-NEW');

        $this->assertDatabaseHas('farmers', ['identifier' => 'AGR-NEW']);
    }

    public function test_duplicate_identifier_is_rejected(): void
    {
        $response = $this->withToken($this->operatorToken)
            ->postJson('/api/farmers', [
                'identifier'   => 'AGR-001', // déjà existant
                'firstname'    => 'Test',
                'lastname'     => 'Dup',
                'phone'        => '0788888888',
                'credit_limit' => 50000,
            ]);

        $response->assertStatus(422);
    }

    public function test_farmer_debt_summary_is_correct(): void
    {
        $farmer = Farmer::where('identifier', 'AGR-001')->first();

        $response = $this->withToken($this->operatorToken)
            ->getJson("/api/farmers/{$farmer->id}/debts");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => ['farmer', 'total_debt', 'open_debts'],
                 ]);
    }
}