<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Item;
use App\Models\StockEntry;
use App\Models\StockLedgerEntry;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockEntryTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $warehouse;
    protected $targetWarehouse;
    protected $item;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->company = Company::firstOrCreate(
            ['name' => 'Test Company'],
            ['abbr' => 'TC', 'default_currency' => 'USD']
        );

        $this->warehouse = Warehouse::firstOrCreate(
            ['name' => 'Source Warehouse'],
            [
                'warehouse_name' => 'Source Warehouse',
                'company_id' => $this->company->id,
                'warehouse_type' => 'Store'
            ]
        );

        $this->targetWarehouse = Warehouse::firstOrCreate(
            ['name' => 'Target Warehouse'],
            [
                'warehouse_name' => 'Target Warehouse',
                'company_id' => $this->company->id,
                'warehouse_type' => 'Store'
            ]
        );

        $this->item = Item::firstOrCreate(
            ['item_code' => 'ITEM001'],
            [
                'item_name' => 'Test Item',
                'item_group' => 'All Item Groups',
                'stock_uom' => 'Nos',
                'is_stock_item' => 1,
                'valuation_method' => 'FIFO',
                'standard_rate' => 100,
            ]
        );
    }

    public function test_create_stock_entry_material_receipt()
    {
        $response = $this->actingAs($this->user)->postJson('/api/stock-entries', [
            'company_id' => $this->company->id,
            'stock_entry_type' => 'Material Receipt',
            'purpose' => 'Material Receipt',
            'posting_date' => now()->toDateString(),
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'qty' => 10,
                    't_warehouse_id' => $this->warehouse->id,
                    'basic_rate' => 100,
                    'basic_amount' => 1000,
                ]
            ]
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('stock_entry', ['stock_entry_type' => 'Material Receipt']);
    }

    public function test_submit_stock_entry_material_receipt()
    {
        // Create
        $entry = StockEntry::create([
            'name' => 'STE-TEST-001',
            'company_id' => $this->company->id,
            'stock_entry_type' => 'Material Receipt',
            'purpose' => 'Material Receipt',
            'posting_date' => now()->toDateString(),
            'docstatus' => 0
        ]);

        $entry->itemsRelation()->create([
            'item_id' => $this->item->id,
            'qty' => 10,
            't_warehouse_id' => $this->warehouse->id,
            'basic_rate' => 100,
            'basic_amount' => 1000,
        ]);

        // Submit
        $response = $this->actingAs($this->user)->postJson("/api/stock-entries/{$entry->name}/submit");

        $response->assertStatus(200);
        $this->assertDatabaseHas('stock_entry', ['id' => $entry->id, 'docstatus' => 1]);

        // Check Ledger Entry
        $this->assertDatabaseHas('stock_ledger_entries', [
            'voucher_type' => 'Stock Entry',
            // 'voucher_no' => 'STE-TEST-001', // might match
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'actual_qty' => 10 // Positive for receipt
        ]);
    }

    public function test_submit_stock_entry_material_issue_no_stock()
    {
        // Issue 5 items from Source (empty)
        $entry = StockEntry::create([
            'name' => 'STE-TEST-002',
            'company_id' => $this->company->id,
            'stock_entry_type' => 'Material Issue',
            'purpose' => 'Material Issue',
            'posting_date' => now()->toDateString(),
            'docstatus' => 0
        ]);

        $entry->itemsRelation()->create([
            'item_id' => $this->item->id,
            'qty' => 5,
            's_warehouse_id' => $this->warehouse->id,
            'basic_rate' => 100,
            'basic_amount' => 500,
        ]);

        // Submit - Should Fail 400
        $response = $this->actingAs($this->user)->postJson("/api/stock-entries/{$entry->name}/submit");

        $response->assertStatus(400);
        $response->assertJsonFragment(['error' => "Insufficient stock for Item ID {$this->item->id} in Warehouse ID {$this->warehouse->id}. Available: 0, Required: 5.00"]);
    }

    public function test_submit_stock_entry_material_issue_success()
    {
        // 1. Add Stock (Manual Ledger Entry)
        StockLedgerEntry::create([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'company_id' => $this->company->id,
            'posting_date' => now()->subDay()->toDateString(),
            'posting_time' => '12:00:00',
            'actual_qty' => 10,
            'qty_after_transaction' => 10,
            'stock_value' => 1000,
            'valuation_rate' => 100,
            'voucher_type' => 'Opening',
            'voucher_no' => 'OP-001'
        ]);

        // 2. Issue 5 items
        $entry = StockEntry::create([
            'name' => 'STE-TEST-003',
            'company_id' => $this->company->id,
            'stock_entry_type' => 'Material Issue',
            'purpose' => 'Material Issue',
            'posting_date' => now()->toDateString(),
            'docstatus' => 0
        ]);

        $entry->itemsRelation()->create([
            'item_id' => $this->item->id,
            'qty' => 5,
            's_warehouse_id' => $this->warehouse->id,
            'basic_rate' => 100,
            'basic_amount' => 500,
        ]);

        // Submit
        $response = $this->actingAs($this->user)->postJson("/api/stock-entries/{$entry->name}/submit");

        $response->assertStatus(200);

        // Check Ledger Entry (Negative)
        $this->assertDatabaseHas('stock_ledger_entries', [
            'voucher_no' => 'STE-TEST-003',
            'warehouse_id' => $this->warehouse->id,
            'actual_qty' => -5
        ]);
    }
}
