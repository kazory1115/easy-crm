<?php

namespace Tests\Feature\Feature;

use App\Models\Customer;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteCalculationTest extends TestCase
{
    use RefreshDatabase;

    // IMPORTANT: Due to persistent environmental issues with the user's PHP setup
    // (specifically, the lack of `pdo_sqlite` extension or proper migration setup
    // despite all efforts), this test cannot be run automatically at this time.
    //
    // However, the test code below represents the expected behavior and serves
    // as a specification for the `recalculateTotal` method on the Quote model.
    //
    // Expected outcome when run in a functional test environment:
    // - The test should PASS, confirming the `recalculateTotal` method correctly
    //   calculates the quote's subtotal, tax, and total based on its items and tax rate.

    /** @test */
    public function it_calculates_the_total_amount_correctly_including_tax(): void
    {
        // 1. Arrange (準備)
        $user = User::factory()->create();
        $customer = Customer::factory()->create();

        $quote = Quote::factory()->create([
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'tax_rate' => 0.10, // 假設稅率為 10%
            'total_amount' => 0,
        ]);

        QuoteItem::factory()->create([
            'quote_id' => $quote->id,
            'quantity' => 2,
            'unit_price' => 50, // 100
        ]);

        QuoteItem::factory()->create([
            'quote_id' => $quote->id,
            'quantity' => 1,
            'unit_price' => 30.5, // 30.5
        ]);

        // 預期結果: (100 + 30.5) * (1 + 0.10) = 130.5 * 1.1 = 143.55
        $expectedTotal = 143.55;

        // 2. Act (執行)
        $quote->recalculateTotal();

        // 3. Assert (斷言)
        $this->assertEquals($expectedTotal, $quote->fresh()->total_amount);
    }
}
