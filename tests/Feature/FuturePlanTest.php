<?php

namespace Tests\Feature;

use App\Models\FuturePlan;
use App\Models\FuturePlanItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FuturePlanTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['status' => 'approved']);
    }

    public function test_guest_cannot_access_future_planner(): void
    {
        $response = $this->get(route('future-planner.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_future_planner_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('future-planner.index'));

        $response->assertStatus(200);
        $response->assertSee('Future Planner');
        $response->assertSee('Buat Rencana Baru');
    }

    public function test_user_can_create_a_future_plan(): void
    {
        $response = $this->actingAs($this->user)->post(route('future-planner.store'), [
            'title'       => 'Setup Ruang Kerja 2027',
            'description' => 'Target meja, monitor, dan kursi ergonomis',
            'target_date' => '2027-06-30',
            'status'      => 'planned',
        ]);

        $plan = FuturePlan::where('title', 'Setup Ruang Kerja 2027')->first();
        $this->assertNotNull($plan);
        $this->assertEquals($this->user->id, $plan->user_id);

        $response->assertRedirect(route('future-planner.show', $plan));
        $response->assertSessionHas('success');
    }

    public function test_user_can_view_plan_detail_with_items_and_grand_total(): void
    {
        $plan = FuturePlan::create([
            'user_id' => $this->user->id,
            'title'   => 'Liburan ke Jepang',
        ]);

        FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'title'          => 'Tiket Pesawat',
            'quantity'       => 2,
            'unit_price'     => 7500000,
            'is_completed'   => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('future-planner.show', $plan));

        $response->assertStatus(200);
        $response->assertSee('Liburan ke Jepang');
        $response->assertSee('Tiket Pesawat');
        $response->assertSee('TOTAL KESELURUHAN ITEM (GRAND TOTAL)');
        $response->assertSee('15.000.000'); // 2 * 7.5jt
    }

    public function test_user_can_add_item_and_sub_item_to_plan(): void
    {
        $plan = FuturePlan::create([
            'user_id' => $this->user->id,
            'title'   => 'Upgrade PC Gaming',
        ]);

        // 1. Root Item: Hardware
        $responseRoot = $this->actingAs($this->user)->post(route('future-planner.items.store', $plan), [
            'title'      => 'Hardware Utama',
            'quantity'   => 1,
            'unit_price' => 0,
        ]);
        $responseRoot->assertSessionHas('success');

        $rootItem = FuturePlanItem::where('future_plan_id', $plan->id)->where('title', 'Hardware Utama')->first();
        $this->assertNotNull($rootItem);
        $this->assertNull($rootItem->parent_id);

        // 2. Sub Item di bawah Hardware Utama
        $responseSub = $this->actingAs($this->user)->post(route('future-planner.items.store', $plan), [
            'parent_id'  => $rootItem->id,
            'title'      => 'VGA RTX 4070',
            'quantity'   => 1,
            'unit_price' => 11000000,
        ]);
        $responseSub->assertSessionHas('success');

        $subItem = FuturePlanItem::where('future_plan_id', $plan->id)->where('title', 'VGA RTX 4070')->first();
        $this->assertNotNull($subItem);
        $this->assertEquals($rootItem->id, $subItem->parent_id);
    }

    public function test_user_can_toggle_item_status_via_ajax(): void
    {
        $plan = FuturePlan::create([
            'user_id' => $this->user->id,
            'title'   => 'Rencana Belanja',
        ]);

        $item = FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'title'          => 'Keyboard Mechanical',
            'quantity'       => 1,
            'unit_price'     => 1500000,
            'is_completed'   => false,
        ]);

        $response = $this->actingAs($this->user)->patchJson(route('future-planner.items.toggle', [$plan, $item]));

        $response->assertStatus(200);
        $response->assertJson([
            'success'      => true,
            'is_completed' => true,
        ]);

        $this->assertTrue($item->fresh()->is_completed);
    }

    public function test_user_can_reparent_item_with_reparent_endpoint(): void
    {
        $plan = FuturePlan::create([
            'user_id' => $this->user->id,
            'title'   => 'Rencana Indent Outdent',
        ]);

        $itemA = FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'title'          => 'Item Induk A',
        ]);

        $itemB = FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'title'          => 'Item B (Awalnya Root)',
        ]);

        // Jadikan item B sebagai anak dari item A (Indent)
        $responseIndent = $this->actingAs($this->user)->patchJson(route('future-planner.items.reparent', [$plan, $itemB]), [
            'parent_id' => $itemA->id,
        ]);
        $responseIndent->assertStatus(200);
        $this->assertEquals($itemA->id, $itemB->fresh()->parent_id);

        // Kembalikan item B ke root (Outdent)
        $responseOutdent = $this->actingAs($this->user)->patchJson(route('future-planner.items.reparent', [$plan, $itemB]), [
            'parent_id' => null,
        ]);
        $responseOutdent->assertStatus(200);
        $this->assertNull($itemB->fresh()->parent_id);
    }

    public function test_user_cannot_set_descendant_as_parent_item(): void
    {
        $plan = FuturePlan::create([
            'user_id' => $this->user->id,
            'title'   => 'Rencana Hierarchy',
        ]);

        $parent = FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'title'          => 'Parent Item',
        ]);

        $child = FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'parent_id'      => $parent->id,
            'title'          => 'Child Item',
        ]);

        // Coba jadikan child sebagai parent dari parent-nya
        $response = $this->actingAs($this->user)->put(route('future-planner.items.update', [$plan, $parent]), [
            'parent_id' => $child->id,
            'title'     => 'Parent Item',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_user_can_delete_item_and_sub_items_cascade(): void
    {
        $plan = FuturePlan::create([
            'user_id' => $this->user->id,
            'title'   => 'Rencana Hapus',
        ]);

        $parent = FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'title'          => 'Induk Item',
        ]);

        $child = FuturePlanItem::create([
            'future_plan_id' => $plan->id,
            'parent_id'      => $parent->id,
            'title'          => 'Sub Item',
        ]);

        $response = $this->actingAs($this->user)->delete(route('future-planner.items.destroy', [$plan, $parent]));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('future_plan_items', ['id' => $parent->id]);
        $this->assertDatabaseMissing('future_plan_items', ['id' => $child->id]);
    }

    public function test_user_cannot_modify_other_users_plan_or_items(): void
    {
        $otherUser = User::factory()->create();
        $otherPlan = FuturePlan::create([
            'user_id' => $otherUser->id,
            'title'   => 'Rencana Rahasia Orang Lain',
        ]);

        $otherItem = FuturePlanItem::create([
            'future_plan_id' => $otherPlan->id,
            'title'          => 'Item Rahasia',
        ]);

        // Coba akses show
        $this->actingAs($this->user)->get(route('future-planner.show', $otherPlan))->assertStatus(403);

        // Coba delete plan
        $this->actingAs($this->user)->delete(route('future-planner.destroy', $otherPlan))->assertStatus(403);

        // Coba delete item
        $this->actingAs($this->user)->delete(route('future-planner.items.destroy', [$otherPlan, $otherItem]))->assertStatus(403);
    }

    public function test_api_endpoints_work_properly(): void
    {
        // 1. API Store Plan
        $storePlanResponse = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/future-planner', [
            'title'       => 'Plan API Mobile',
            'description' => 'Dibuat via Kotlin/Flutter app',
        ]);
        $storePlanResponse->assertStatus(201);
        $planId = $storePlanResponse->json('data.id');

        // 2. API Store Item
        $storeItemResponse = $this->actingAs($this->user, 'sanctum')->postJson("/api/v1/future-planner/{$planId}/items", [
            'title'      => 'Item API A',
            'quantity'   => 2,
            'unit_price' => 500000,
        ]);
        $storeItemResponse->assertStatus(201);
        $itemId = $storeItemResponse->json('data.id');

        // 3. API Show Plan with Items & Grand Total
        $showResponse = $this->actingAs($this->user, 'sanctum')->getJson("/api/v1/future-planner/{$planId}");
        $showResponse->assertStatus(200);
        $showResponse->assertJsonPath('data.plan.title', 'Plan API Mobile');
        $showResponse->assertJsonPath('data.plan.total_estimated_amount', 1000000);
        $this->assertCount(1, $showResponse->json('data.items'));

        // 4. API Toggle Item
        $toggleResponse = $this->actingAs($this->user, 'sanctum')->patchJson("/api/v1/future-planner/{$planId}/items/{$itemId}/toggle");
        $toggleResponse->assertStatus(200);
        $toggleResponse->assertJsonPath('data.is_completed', true);

        // 5. API Destroy Item
        $deleteItemResponse = $this->actingAs($this->user, 'sanctum')->deleteJson("/api/v1/future-planner/{$planId}/items/{$itemId}");
        $deleteItemResponse->assertStatus(200);

        // 6. API Destroy Plan
        $deletePlanResponse = $this->actingAs($this->user, 'sanctum')->deleteJson("/api/v1/future-planner/{$planId}");
        $deletePlanResponse->assertStatus(200);
        $this->assertNull(FuturePlan::find($planId));
    }
}
