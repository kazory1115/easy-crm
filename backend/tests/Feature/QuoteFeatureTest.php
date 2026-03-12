<?php

namespace Tests\Feature;

use App\Mail\QuoteMail;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class QuoteFeatureTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithAccess(['admin']);
    }

    private function quotePayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => '測試客戶',
            'contact_phone' => '0912345678',
            'contact_email' => 'customer@example.com',
            'project_name' => '測試專案',
            'quote_date' => now()->toDateString(),
            'notes' => '測試備註',
            'items' => [
                [
                    'type' => 'input',
                    'name' => '測試品項',
                    'description' => '測試描述',
                    'quantity' => 2,
                    'unit' => '組',
                    'price' => 100,
                ],
            ],
        ], $overrides);
    }

    public function test_unauthorized_user_cannot_access_quote_index(): void
    {
        $this->getJson('/api/quotes')->assertUnauthorized();
    }

    public function test_can_get_quote_index(): void
    {
        Sanctum::actingAs($this->user);

        Quote::factory()->count(2)->create([
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $this->getJson('/api/quotes')
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'last_page', 'total'])
            ->assertJsonPath('total', 2);
    }

    public function test_can_store_quote(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/quotes', $this->quotePayload())
            ->assertCreated()
            ->assertJsonPath('message', '報價單建立成功')
            ->assertJsonPath('data.customer_name', '測試客戶');

        $this->assertDatabaseHas('quotes', [
            'customer_name' => '測試客戶',
            'created_by' => $this->user->id,
        ]);
        $this->assertDatabaseCount('quote_items', 1);
    }

    public function test_can_update_quote(): void
    {
        Sanctum::actingAs($this->user);

        $quote = Quote::factory()->create([
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        QuoteItem::create([
            'quote_id' => $quote->id,
            'type' => 'input',
            'name' => '舊品項',
            'quantity' => 1,
            'unit' => '組',
            'price' => 50,
            'sort_order' => 1,
        ]);

        $payload = $this->quotePayload([
            'customer_name' => '更新後客戶',
            'items' => [
                [
                    'type' => 'input',
                    'name' => '更新後品項',
                    'quantity' => 3,
                    'unit' => '組',
                    'price' => 200,
                ],
            ],
        ]);

        $this->putJson("/api/quotes/{$quote->id}", $payload)
            ->assertOk()
            ->assertJsonPath('message', '報價單更新成功')
            ->assertJsonPath('data.customer_name', '更新後客戶');

        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'customer_name' => '更新後客戶',
            'updated_by' => $this->user->id,
        ]);
    }

    public function test_can_delete_quote(): void
    {
        Sanctum::actingAs($this->user);

        $quote = Quote::factory()->create([
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $this->deleteJson("/api/quotes/{$quote->id}")
            ->assertOk()
            ->assertJsonPath('message', '報價單已刪除');

        $this->assertSoftDeleted('quotes', ['id' => $quote->id]);
    }

    public function test_can_update_quote_status(): void
    {
        Sanctum::actingAs($this->user);

        $quote = Quote::factory()->draft()->create([
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $this->patchJson("/api/quotes/{$quote->id}/status", [
            'status' => 'approved',
        ])->assertOk()
            ->assertJsonPath('message', '狀態更新成功')
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'status' => 'approved',
        ]);
    }

    public function test_can_download_quote_pdf_and_excel(): void
    {
        Sanctum::actingAs($this->user);

        $quote = Quote::factory()->create([
            'customer_name' => '下載客戶',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        QuoteItem::create([
            'quote_id' => $quote->id,
            'type' => 'input',
            'name' => '下載品項',
            'quantity' => 2,
            'unit' => '組',
            'price' => 1200,
            'sort_order' => 1,
        ]);

        $this->get("/api/quotes/{$quote->id}/pdf")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->get("/api/quotes/{$quote->id}/excel")
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_can_send_quote_email(): void
    {
        Sanctum::actingAs($this->user);
        Mail::fake();

        $quote = Quote::factory()->draft()->create([
            'contact_email' => 'original@example.com',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        QuoteItem::create([
            'quote_id' => $quote->id,
            'type' => 'input',
            'name' => '寄信品項',
            'quantity' => 1,
            'unit' => '組',
            'price' => 500,
            'sort_order' => 1,
        ]);

        $this->postJson("/api/quotes/{$quote->id}/send", [
            'email' => 'receiver@example.com',
            'subject' => '正式報價單',
            'message' => '請查收附件',
        ])->assertOk()
            ->assertJsonPath('message', '報價單已寄出')
            ->assertJsonPath('data.status', 'sent');

        Mail::assertSent(QuoteMail::class, function (QuoteMail $mail): bool {
            return $mail->hasTo('receiver@example.com');
        });

        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'status' => 'sent',
        ]);
    }
}
