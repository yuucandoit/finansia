<?php

namespace Tests\Unit;

use App\Models\FuturePlan;
use App\Models\FuturePlanItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FuturePlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_leaf_item_calculates_subtotal_from_quantity_and_price(): void
    {
        $user = User::factory()->create(['status' => 'approved']);
        $plan = FuturePlan::create([
            'user_id' => $user->id,
            'title'   => 'Setup Ruang Kerja',
        ]);

        $item = FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'title'          => 'Monitor 27 Inch 4K',
            'quantity'       => 2,
            'unit_price'     => 4500000,
            'is_completed'   => false,
        ]);

        $this->assertEquals(9000000.0, $item->subtotal);
        $this->assertEquals(9000000.0, $item->effective_subtotal);
        $this->assertEquals(0.0, $item->effective_completed_subtotal);
    }

    public function test_parent_item_rolls_up_effective_subtotal_from_children(): void
    {
        $user = User::factory()->create(['status' => 'approved']);
        $plan = FuturePlan::create([
            'user_id' => $user->id,
            'title'   => 'Renovasi Kamar',
        ]);

        // Parent group item (Furniture)
        $parent = FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'title'          => 'Furniture & Ergonomi',
            'quantity'       => 1,
            'unit_price'     => 0,
        ]);

        // Child 1: Kursi (qty 1, harga 3jt)
        FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'parent_id'      => $parent->id,
            'title'          => 'Kursi Ergonomis',
            'quantity'       => 1,
            'unit_price'     => 3000000,
            'is_completed'   => true,
        ]);

        // Child 2: Standing Desk (qty 1, harga 5jt)
        FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'parent_id'      => $parent->id,
            'title'          => 'Standing Desk Otomatis',
            'quantity'       => 1,
            'unit_price'     => 5000000,
            'is_completed'   => false,
        ]);

        $parent->refresh();

        // Rollup: 3jt + 5jt = 8jt
        $this->assertEquals(8000000.0, $parent->effective_subtotal);
        // Completed subtotal: 3jt (kursi sudah terbeli)
        $this->assertEquals(3000000.0, $parent->effective_completed_subtotal);

        // Plan level totals
        $plan->refresh();
        $this->assertEquals(8000000.0, $plan->total_estimated_amount);
        $this->assertEquals(3000000.0, $plan->total_completed_amount);
        $this->assertEquals(5000000.0, $plan->remaining_amount);
        $this->assertEquals(37.5, $plan->progress_percentage);
    }

    public function test_multi_level_item_tree_rolls_up_recursively(): void
    {
        $user = User::factory()->create(['status' => 'approved']);
        $plan = FuturePlan::create([
            'user_id' => $user->id,
            'title'   => 'Pembangunan Studio Musik',
        ]);

        // L1
        $l1 = FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'title'          => 'Perangkat Audio',
            'quantity'       => 1,
            'unit_price'     => 0,
        ]);

        // L2 (Child dari L1, Parent bagi L3)
        $l2 = FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'parent_id'      => $l1->id,
            'title'          => 'Speaker Monitor & Aksesoris',
            'quantity'       => 1,
            'unit_price'     => 0,
        ]);

        // L3a: Pair Monitor Speaker (qty 1, 10jt)
        FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'parent_id'      => $l2->id,
            'title'          => 'Yamaha HS8 Pair',
            'quantity'       => 1,
            'unit_price'     => 10000000,
            'is_completed'   => true,
        ]);

        // L3b: Isolation Pads (qty 2, @500rb = 1jt)
        FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'parent_id'      => $l2->id,
            'title'          => 'Acoustic Foam Pad',
            'quantity'       => 2,
            'unit_price'     => 500000,
            'is_completed'   => false,
        ]);

        $l2->refresh();
        $l1->refresh();

        // L2: 10jt + 1jt = 11jt
        $this->assertEquals(11000000.0, $l2->effective_subtotal);
        // L1: mewarisi 11jt
        $this->assertEquals(11000000.0, $l1->effective_subtotal);
    }

    public function test_flat_tree_for_plan_organizes_items_in_depth_first_order(): void
    {
        $user = User::factory()->create(['status' => 'approved']);
        $plan = FuturePlan::create([
            'user_id' => $user->id,
            'title'   => 'Proyek Liburan',
        ]);

        $rootA = FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'title'          => 'Tiket Transportasi',
            'order'          => 1,
        ]);

        $childA1 = FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'parent_id'      => $rootA->id,
            'title'          => 'Tiket Pesawat PP',
            'order'          => 1,
        ]);

        $grandchildA1_1 = FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'parent_id'      => $childA1->id,
            'title'          => 'Bagasi Tambahan 20kg',
            'order'          => 1,
        ]);

        $rootB = FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'title'          => 'Akomodasi Hotel',
            'order'          => 2,
        ]);

        $tree = FuturePlanItem::getFlatTreeForPlan($plan->id);

        $this->assertCount(4, $tree);
        // Depth-first order: Root A (0) -> Child A1 (1) -> Grandchild A1.1 (2) -> Root B (0)
        $this->assertEquals('Tiket Transportasi', $tree[0]->title);
        $this->assertEquals(0, $tree[0]->depth);

        $this->assertEquals('Tiket Pesawat PP', $tree[1]->title);
        $this->assertEquals(1, $tree[1]->depth);

        $this->assertEquals('Bagasi Tambahan 20kg', $tree[2]->title);
        $this->assertEquals(2, $tree[2]->depth);

        $this->assertEquals('Akomodasi Hotel', $tree[3]->title);
        $this->assertEquals(0, $tree[3]->depth);
    }
}
