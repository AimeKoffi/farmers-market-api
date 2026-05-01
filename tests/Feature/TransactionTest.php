<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Farmer;
use App\Models\Product;
use App\Models\Setting;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    private User $operator;
    private string $token;
    private Farmer $farmer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::where('role', 'operator')->first();
        $this->token    = $this->operator->createToken('test')->plainTextToken;
        $this->farmer   = Farmer::first();
        $this->product  = Product::first();
    }

    public function test_cash_transaction_is_created_successfully(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/transactions', [
                'farmer_id'      => $this->farmer->id,
                'payment_method' => 'cash',
                'items'          => [[
                    'product_id' => $this->product->id,
                    'quantity'   => 2,
                    'unit_price' => (float) $this->product->price_fcfa,
                ]],
            ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.payment_method', 'cash')
                 ->assertJsonPath('data.interest_rate', 0);

        $this->assertDatabaseHas('transactions', [
            'farmer_id'      => $this->farmer->id,
            'payment_method' => 'cash',
        ]);
    }

    public function test_credit_transaction_applies_interest(): void
    {
        Setting::updateOrCreate(['key' => 'interest_rate'], ['value' => '0.30']);
        $price = (float) $this->product->price_fcfa;

        $response = $this->withToken($this->token)
            ->postJson('/api/transactions', [
                'farmer_id'      => $this->farmer->id,
                'payment_method' => 'credit',
                'items'          => [[
                    'product_id' => $this->product->id,
                    'quantity'   => 1,
                    'unit_price' => $price,
                ]],
            ]);

        $response->assertStatus(201);

        $data = $response->json('data');
        $expected = round($price * 1.30, 2);
        $this->assertEquals($expected, $data['total_with_interest']);

        // Une dette doit être créée
        $this->assertDatabaseHas('debts', [
            'farmer_id' => $this->farmer->id,
            'status'    => 'open',
        ]);
    }

    public function test_credit_transaction_blocked_when_limit_exceeded(): void
    {
        // Farmer avec limite très basse
        $farmer = Farmer::factory()->create([
            'credit_limit' => 100, // 100 FCFA seulement
        ]);

        $response = $this->withToken($this->token)
            ->postJson('/api/transactions', [
                'farmer_id'      => $farmer->id,
                'payment_method' => 'credit',
                'items'          => [[
                    'product_id' => $this->product->id,
                    'quantity'   => 10,
                    'unit_price' => (float) $this->product->price_fcfa,
                ]],
            ]);

        $response->assertStatus(422)
                 ->assertJson(['success' => false]);

        // Aucune transaction créée
        $this->assertDatabaseMissing('transactions', [
            'farmer_id' => $farmer->id,
        ]);
    }

    public function test_transaction_requires_at_least_one_item(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/transactions', [
                'farmer_id'      => $this->farmer->id,
                'payment_method' => 'cash',
                'items'          => [],
            ]);

        $response->assertStatus(422);
    }

    public function test_operator_can_view_own_transactions(): void
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/transactions');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }
}