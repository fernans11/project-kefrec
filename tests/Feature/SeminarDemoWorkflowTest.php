<?php

namespace Tests\Feature;

use App\Models\Cashflow;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\DemoScenarioSeeder;
use Database\Seeders\IngredientSeeder;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeminarDemoWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([MenuSeeder::class, IngredientSeeder::class, DemoScenarioSeeder::class]);
    }

    public function test_all_demo_accounts_exist_and_open_their_own_workspaces(): void
    {
        $admin = User::where('email', 'admin.demo@kefrec.test')->firstOrFail();
        $owner = User::where('email', 'owner.demo@kefrec.test')->firstOrFail();
        $cashier = User::where('email', 'kasir.demo@kefrec.test')->firstOrFail();
        $kitchen = User::where('email', 'dapur.demo@kefrec.test')->firstOrFail();

        $this->assertSame(50, User::where('email', 'like', 'member%@kefrec.test')->count());
        $this->actingAs($admin, 'admin')->get('/admin')->assertOk();
        $this->freshSession();
        $this->actingAs($owner, 'admin')->get('/admin')->assertOk();
        $this->freshSession();
        $this->actingAs($cashier, 'web')->get('/cashier/orders')->assertOk();
        $this->freshSession();
        $this->actingAs($kitchen, 'web')->get('/kitchen/orders')->assertOk();
        $this->actingAs($kitchen, 'web')->get('/kitchen/ingredients')->assertOk();
    }

    public function test_member_cash_order_really_flows_from_cashier_to_kitchen_and_updates_inventory(): void
    {
        $member = User::where('email', 'member01@kefrec.test')->firstOrFail();
        $cashier = User::where('email', 'kasir.demo@kefrec.test')->firstOrFail();
        $kitchen = User::where('email', 'dapur.demo@kefrec.test')->firstOrFail();
        $product = Product::where('name', 'Cappuccino')->firstOrFail();
        $coffee = Ingredient::where('name', 'Kopi Arabica')->firstOrFail();
        $stockBefore = (float) $coffee->stock;

        $response = $this->actingAs($member, 'web')->postJson('/checkout', [
            'payment_method' => 'cash',
            'paid_amount' => 100000,
            'table_number' => 'A-07',
            'items' => [['product_id' => $product->id, 'qty' => 2]],
        ])->assertCreated()->assertJsonPath('status', 'pending_cashier');

        $transaction = Transaction::findOrFail($response->json('transaction_id'));
        $this->assertSame($member->customer->id, $transaction->customer_id);
        $this->assertCount(1, $transaction->items);

        $this->freshSession();
        $this->actingAs($cashier, 'web')
            ->get(route('cashier.orders.index'))
            ->assertOk()
            ->assertSee('Meja: A-07');

        $this->actingAs($cashier, 'web')
            ->post(route('cashier.orders.approve', $transaction))
            ->assertSessionHas('success');
        $this->assertSame('processing', $transaction->fresh()->status);
        $this->assertSame($cashier->id, $transaction->fresh()->cashier_id);

        $this->freshSession();
        $this->actingAs($kitchen, 'web')
            ->post(route('kitchen.orders.ready', $transaction))
            ->assertSessionHas('success');
        $this->assertSame('ready', $transaction->fresh()->status);

        $this->actingAs($kitchen, 'web')
            ->post(route('kitchen.orders.completed', $transaction))
            ->assertSessionHas('success');

        $transaction->refresh();
        $this->assertSame('completed', $transaction->status);
        $this->assertNotNull($transaction->stock_deducted_at);
        $this->assertSame($stockBefore - 20, (float) $coffee->fresh()->stock);
        $this->assertTrue(Cashflow::where('source', 'sales')->where('notes', 'Penjualan '.$transaction->invoice_no)->exists());
    }

    public function test_wrong_roles_are_rejected_and_invalid_status_actions_do_not_change_orders(): void
    {
        $member = User::where('email', 'member02@kefrec.test')->firstOrFail();
        $cashier = User::where('email', 'kasir.demo@kefrec.test')->firstOrFail();
        $kitchen = User::where('email', 'dapur.demo@kefrec.test')->firstOrFail();
        $processing = Transaction::where('invoice_no', 'like', 'INV-DEMO-LIVE-%')
            ->where('status', 'processing')
            ->firstOrFail();

        $this->actingAs($member, 'web')->get('/cashier/orders')->assertForbidden();
        $this->freshSession();
        $this->actingAs($cashier, 'web')->get('/kitchen/orders')->assertForbidden();
        $this->freshSession();
        $this->actingAs($kitchen, 'web')->get('/cashier/orders')->assertForbidden();

        $this->actingAs($kitchen, 'web')
            ->post(route('kitchen.orders.completed', $processing))
            ->assertSessionHas('error');
        $this->assertSame('processing', $processing->fresh()->status);
    }

    public function test_member_can_only_view_their_own_order_and_can_submit_a_complaint(): void
    {
        $member = User::where('email', 'member03@kefrec.test')->firstOrFail();
        $otherMember = User::where('email', 'member04@kefrec.test')->firstOrFail();
        $order = Transaction::where('customer_id', $member->customer->id)->firstOrFail();

        $this->actingAs($member, 'web')->get(route('orders.show', $order))->assertOk();
        $this->freshSession();
        $this->actingAs($otherMember, 'web')->get(route('orders.show', $order))->assertForbidden();

        $this->freshSession();
        $this->actingAs($member, 'web')->post(route('member.complaints.store'), [
            'title' => 'Pengujian komplain saat seminar',
            'message' => 'Komplain pengujian ini harus tersimpan untuk diperiksa admin.',
        ])->assertSessionHas('success');

        $this->assertTrue(DB::table('complaints')
            ->where('customer_id', $member->customer->id)
            ->where('title', 'Pengujian komplain saat seminar')
            ->exists());
    }

    private function freshSession(): void
    {
        $this->app['auth']->forgetGuards();
        $this->app['auth']->shouldUse('web');
        $this->flushSession();
    }
}
