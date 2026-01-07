<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Item;
use App\Models\SalesOrder;
use App\Models\StockLedgerEntry;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesOrderTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $customer;
    protected $warehouse;
    protected $item;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create basic dependencies
        $this->user = User::factory()->create();

        $this->company = Company::firstOrCreate(
            ['name' => 'Test Company'],
            ['abbr' => 'TC', 'default_currency' => 'USD']
        );

        $this->customer = Customer::firstOrCreate(
            ['name' => 'Test Customer'],
            [
                'customer_name' => 'Test Customer',
                'company_id' => $this->company->id,
                'customer_group' => 'All',
                'customer_type' => 'Company',
                'credit_limit' => 1000,
            ]
        );

        $this->warehouse = Warehouse::firstOrCreate(
            ['name' => 'Test Warehouse'],
            [
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

    public function test_create_sales_order_success()
    {
        $response = $this->actingAs($this->user)->postJson('/api/sales-orders', [
            'customer_id' => $this->customer->id,
            'company_id' => $this->company->id,
            'transaction_date' => now()->toDateString(),
            'delivery_date' => now()->addDays(5)->toDateString(),
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'qty' => 5,
                    'rate' => 100,
                    'amount' => 500,
                    'warehouse_id' => $this->warehouse->id
                ]
            ]
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('sales_order', ['customer_id' => $this->customer->id, 'docstatus' => 0]);
    }

    public function test_submit_sales_order_stock_error()
    {
        // Create SO
        $so = SalesOrder::create([
            'name' => 'SO-TEST-001',
            'customer_id' => $this->customer->id,
            'company_id' => $this->company->id,
            'transaction_date' => now()->toDateString(),
            'delivery_date' => now()->addDays(5)->toDateString(),
            'total_qty' => 10,
            'grand_total' => 1000,
            'docstatus' => 0
        ]);

        $so->itemsRelation()->create([
            'item_id' => $this->item->id,
            'qty' => 10,
            'rate' => 100,
            'amount' => 1000,
            'warehouse_id' => $this->warehouse->id
        ]);

        // Submit (Should fail because stock is 0)
        $response = $this->actingAs($this->user)->postJson("/api/sales-orders/{$so->name}/submit");

        $response->assertStatus(400);
        $response->assertJsonFragment(['error' => "Insufficient stock for Item ID {$this->item->id} in Warehouse ID {$this->warehouse->id}. Available: 0, Required: 10.00"]);
    }

    public function test_submit_sales_order_success_with_stock()
    {
        // Add Stock
        StockLedgerEntry::create([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'posting_date' => now()->subDay()->toDateString(),
            'posting_time' => '12:00:00',
            'actual_qty' => 20,
            'qty_after_transaction' => 20,
            'stock_value' => 2000,
            'valuation_rate' => 100,
            'voucher_type' => 'Stock Entry',
            'voucher_no' => 'STE-OPENING',
            'company_id' => $this->company->id
        ]);

        // Create SO
        $so = SalesOrder::create([
            'name' => 'SO-TEST-002',
            'customer_id' => $this->customer->id,
            'company_id' => $this->company->id,
            'transaction_date' => now()->toDateString(),
            'delivery_date' => now()->addDays(5)->toDateString(),
            'total_qty' => 10,
            'grand_total' => 1000,
            'docstatus' => 0
        ]);

        $so->itemsRelation()->create([
            'item_id' => $this->item->id,
            'qty' => 10,
            'rate' => 100,
            'amount' => 1000,
            'warehouse_id' => $this->warehouse->id
        ]);

        // Submit
        $response = $this->actingAs($this->user)->postJson("/api/sales-orders/{$so->name}/submit");

        $response->assertStatus(200);
        $this->assertDatabaseHas('sales_order', ['id' => $so->id, 'docstatus' => 1]);
    }

    public function test_submit_sales_order_credit_limit_error()
    {
        // Add Stock
        StockLedgerEntry::create([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'posting_date' => now()->subDay()->toDateString(),
            'posting_time' => '12:00:00',
            'actual_qty' => 100,
            'qty_after_transaction' => 100,
            'stock_value' => 10000,
            'valuation_rate' => 100,
            'voucher_type' => 'Stock Entry',
            'voucher_no' => 'STE-OPENING-2',
            'company_id' => $this->company->id
        ]);

        // Create SO exceeding credit limit (Limit 1000, Order 2000)
        $so = SalesOrder::create([
            'name' => 'SO-TEST-003',
            'customer_id' => $this->customer->id,
            'company_id' => $this->company->id,
            'transaction_date' => now()->toDateString(),
            'delivery_date' => now()->addDays(5)->toDateString(),
            'total_qty' => 20,
            'grand_total' => 2000,
            'docstatus' => 0
        ]);

        $so->itemsRelation()->create([
            'item_id' => $this->item->id,
            'qty' => 20,
            'rate' => 100,
            'amount' => 2000,
            'warehouse_id' => $this->warehouse->id
        ]);

        // Submit
        $response = $this->actingAs($this->user)->postJson("/api/sales-orders/{$so->name}/submit");

        $response->assertStatus(400);
        $content = $response->json();
        $this->assertStringContainsString("Credit limit exceeded", $content['error']);
        // Note: The controller threw Exception, Laravel usually wraps exception message in 'message' or 'error' key depending on handler. 
        // I put checked controller: `return response()->json(['error' => $e->getMessage()], 400);` So key is 'error'.
    }
}
