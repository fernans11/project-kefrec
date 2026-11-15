<?php

namespace Tests\Feature;

use App\Models\Cashflow;
use App\Models\Ingredient;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PurchaseReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_received_purchase_adds_ingredient_stock_and_records_cashflow(): void
    {
        $supplier = Supplier::create([
            'name' => 'Supplier Test',
        ]);

        $ingredient = Ingredient::create([
            'name' => 'Kopi Test',
            'unit' => 'gram',
            'stock' => 100,
            'min_stock' => 10,
        ]);

        $purchase = Purchase::create([
            'invoice_no' => 'PO-TEST-001',
            'supplier_id' => $supplier->id,
            'purchased_at' => now(),
            'status' => 'received',
            'subtotal' => 50000,
            'tax' => 0,
            'total' => 50000,
        ]);

        $purchase->items()->create([
            'ingredient_id' => $ingredient->id,
            'qty' => 5,
            'price' => 10000,
            'subtotal' => 50000,
        ]);

        $purchase->applyReceiptIfNeeded();

        $this->assertSame('105.00', $ingredient->fresh()->stock);

        $this->assertDatabaseHas('cashflows', [
            'type' => 'out',
            'category' => 'Pembelian Bahan Baku',
            'amount' => 50000,
            'source' => 'purchase',
        ]);

        $this->assertStringContainsString('Supplier Test', Cashflow::first()->notes);
        $this->assertStringContainsString('Kopi Test', Cashflow::first()->notes);
    }

    public function test_purchase_totals_are_recalculated_from_items(): void
    {
        $supplier = Supplier::create([
            'name' => 'Supplier Test',
        ]);

        $ingredientA = Ingredient::create([
            'name' => 'Air Test',
            'unit' => 'pcs',
            'stock' => 0,
            'min_stock' => 0,
        ]);

        $ingredientB = Ingredient::create([
            'name' => 'Daging Test',
            'unit' => 'gram',
            'stock' => 0,
            'min_stock' => 0,
        ]);

        $purchase = Purchase::create([
            'invoice_no' => 'PO-TEST-004',
            'supplier_id' => $supplier->id,
            'purchased_at' => now(),
            'status' => 'draft',
            'subtotal' => 0,
            'tax' => 3,
            'total' => 3,
        ]);

        $purchase->items()->createMany([
            [
                'ingredient_id' => $ingredientA->id,
                'qty' => 10000,
                'price' => 5,
                'subtotal' => 0,
            ],
            [
                'ingredient_id' => $ingredientB->id,
                'qty' => 5000,
                'price' => 20,
                'subtotal' => 0,
            ],
        ]);

        $purchase->recalculateTotalsFromItems();

        $purchase->refresh();

        $this->assertSame(150000, $purchase->subtotal);
        $this->assertSame(150003, $purchase->total);
    }

    public function test_received_purchase_cannot_be_deleted(): void
    {
        $supplier = Supplier::create([
            'name' => 'Supplier Test',
        ]);

        $purchase = Purchase::create([
            'invoice_no' => 'PO-TEST-002',
            'supplier_id' => $supplier->id,
            'purchased_at' => now(),
            'status' => 'received',
            'subtotal' => 10000,
            'tax' => 0,
            'total' => 10000,
            'received_at' => now(),
        ]);

        $this->expectException(ValidationException::class);

        $purchase->delete();
    }

    public function test_draft_purchase_can_be_deleted(): void
    {
        $supplier = Supplier::create([
            'name' => 'Supplier Test',
        ]);

        $purchase = Purchase::create([
            'invoice_no' => 'PO-TEST-003',
            'supplier_id' => $supplier->id,
            'purchased_at' => now(),
            'status' => 'draft',
            'subtotal' => 10000,
            'tax' => 0,
            'total' => 10000,
        ]);

        $purchase->delete();

        $this->assertDatabaseMissing('purchases', [
            'invoice_no' => 'PO-TEST-003',
        ]);
    }
}
