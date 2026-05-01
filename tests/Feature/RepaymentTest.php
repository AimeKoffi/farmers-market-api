<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Farmer;
use App\Models\Debt;
use App\Models\Transaction;
use App\Models\Setting;
use Tests\TestCase;

class RepaymentTest extends TestCase
{
    private string $token;
    private Farmer $farmer;

    protected function setUp(): void
    {
        parent::setUp();

        $operator    = User::where('role', 'operator')->first();
        $this->token = $operator->createToken('test')->plainTextToken;
        $this->farmer = Farmer::first();

        Setting::updateOrCreate(
            ['key' => 'commodity_rate'],
            ['value' => '1000']
        );
    }

    private function createDebt(float $amount): Debt
    {
        $tx = Transaction::create([
            'farmer_id'           => $this->farmer->id,
            'operator_id'         => User::where('role', 'operator')->first()->id,
            'total_fcfa'          => $amount,
            'payment_method'      => 'credit',
            'interest_rate'       => 0.30,
            'total_with_interest' => $amount,
        ]);

        return Debt::create([
            'transaction_id' => $tx->id,
            'farmer_id'      => $this->farmer->id,
            'amount_fcfa'    => $amount,
            'remaining_fcfa' => $amount,
            'status'         => 'open',
        ]);
    }

    public function test_repayment_settles_debt_fully(): void
    {
        $debt = $this->createDebt(5000); // 5 kg * 1000 FCFA

        $response = $this->withToken($this->token)
            ->postJson('/api/repayments', [
                'farmer_id'   => $this->farmer->id,
                'kg_received' => 5,
            ]);

        $response->assertStatus(201);

        $debt->refresh();
        $this->assertEquals('paid', $debt->status);
        $this->assertEquals(0, (float) $debt->remaining_fcfa);
    }

    public function test_partial_repayment_keeps_debt_open(): void
    {
        $debt = $this->createDebt(10000); // besoin de 10 kg

        $response = $this->withToken($this->token)
            ->postJson('/api/repayments', [
                'farmer_id'   => $this->farmer->id,
                'kg_received' => 4, // seulement 4 kg = 4000 FCFA
            ]);

        $response->assertStatus(201);

        $debt->refresh();
        $this->assertEquals('partial', $debt->status);
        $this->assertEquals(6000, (float) $debt->remaining_fcfa);
    }

    public function test_fifo_settles_oldest_debt_first(): void
    {
        $debt1 = $this->createDebt(3000); // plus ancienne
        $debt2 = $this->createDebt(5000); // plus récente

        // On rembourse exactement la première dette
        $this->withToken($this->token)
            ->postJson('/api/repayments', [
                'farmer_id'   => $this->farmer->id,
                'kg_received' => 3,
            ]);

        $debt1->refresh();
        $debt2->refresh();

        $this->assertEquals('paid', $debt1->status);
        $this->assertEquals('open', $debt2->status); // intacte
    }

    public function test_repayment_fails_when_no_debts(): void
    {
        // Farmer sans dettes
        $farmer = Farmer::factory()->create();

        $response = $this->withToken($this->token)
            ->postJson('/api/repayments', [
                'farmer_id'   => $farmer->id,
                'kg_received' => 5,
            ]);

        $response->assertStatus(422)
                 ->assertJson(['success' => false]);
    }

    public function test_commodity_rate_is_used_from_settings(): void
    {
        Setting::updateOrCreate(['key' => 'commodity_rate'], ['value' => '2000']);
        $this->createDebt(10000);

        $response = $this->withToken($this->token)
            ->postJson('/api/repayments', [
                'farmer_id'   => $this->farmer->id,
                'kg_received' => 5, // 5 * 2000 = 10000
            ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.commodity_rate', 2000.0)
                 ->assertJsonPath('data.fcfa_value', 10000.0);
    }
}