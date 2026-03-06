<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use Tests\TestCase;

class QuoteCalculationTest extends TestCase
{
    public function test_quote_recalculate_total_uses_quote_item_price_field(): void
    {
        $user = User::factory()->create();

        $quote = Quote::factory()->create([
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'subtotal' => 0,
            'tax' => 0,
            'discount' => 0,
            'total' => 0,
        ]);

        QuoteItem::create([
            'quote_id' => $quote->id,
            'type' => 'input',
            'name' => '項目 A',
            'quantity' => 2,
            'unit' => '式',
            'price' => 100,
            'sort_order' => 1,
        ]);

        QuoteItem::create([
            'quote_id' => $quote->id,
            'type' => 'input',
            'name' => '項目 B',
            'quantity' => 1,
            'unit' => '式',
            'price' => 50,
            'sort_order' => 2,
        ]);

        $quote->recalculateTotal();
        $freshQuote = $quote->fresh();

        $this->assertEquals(250.00, (float) $freshQuote->subtotal);
        $this->assertEquals(0.00, (float) $freshQuote->tax);
        $this->assertEquals(250.00, (float) $freshQuote->total);
    }
}

