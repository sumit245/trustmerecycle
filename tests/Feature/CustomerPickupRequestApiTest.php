<?php

namespace Tests\Feature;

use App\Models\CollectionJob;
use App\Models\Godown;
use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerPickupRequestApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_login_and_read_profile(): void
    {
        $register = $this->postJson('/api/customer/register', [
            'name' => 'Priya Sharma',
            'email' => 'priya@example.com',
            'phone' => '+91 98765 43210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'device_name' => 'test_device',
        ]);

        $register->assertCreated()
            ->assertJsonPath('user.role', 'customer')
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'phone', 'role']]);

        $login = $this->postJson('/api/customer/login', [
            'email' => 'priya@example.com',
            'password' => 'password123',
            'device_name' => 'test_device',
        ]);

        $login->assertOk()
            ->assertJsonPath('user.role', 'customer')
            ->assertJsonStructure(['token']);

        $customer = User::where('email', 'priya@example.com')->firstOrFail();
        Sanctum::actingAs($customer, ['customer']);

        $this->getJson('/api/customer/me')
            ->assertOk()
            ->assertJsonPath('role', 'customer');
    }

    public function test_customer_and_vendor_api_tokens_are_isolated_by_ability(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'phone' => '1111111111']);
        $vendor = User::factory()->create(['role' => 'vendor', 'phone' => '2222222222']);

        Sanctum::actingAs($customer, ['customer']);
        $this->getJson('/api/vendor/jobs')->assertForbidden();

        Sanctum::actingAs($vendor, ['vendor']);
        $this->getJson('/api/customer/pickup-requests')->assertForbidden();
    }

    public function test_vendor_can_list_sites_for_collection_entry(): void
    {
        $vendor = User::factory()->create(['role' => 'vendor', 'phone' => '2222222222']);
        Godown::create([
            'vendor_id' => $vendor->id,
            'name' => 'ES Sant Nagar',
            'location' => 'Sant Nagar',
            'state' => 'Delhi',
            'city' => 'New Delhi',
            'address' => 'Sant Nagar, New Delhi',
            'capacity_limit_mt' => 100,
            'current_stock_mt' => 0,
        ]);

        Sanctum::actingAs($vendor, ['vendor']);

        $this->getJson('/api/vendor/sites')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'ES Sant Nagar')
            ->assertJsonPath('data.0.state', 'Delhi')
            ->assertJsonPath('data.0.city', 'New Delhi');
    }

    public function test_customer_can_create_and_list_only_their_pickup_requests(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'phone' => '1111111111']);
        $otherCustomer = User::factory()->create(['role' => 'customer', 'phone' => '2222222222']);

        PickupRequest::create([
            'user_id' => $otherCustomer->id,
            'customer_name' => $otherCustomer->name,
            'customer_email' => $otherCustomer->email,
            'customer_phone' => $otherCustomer->phone,
            'pickup_address' => 'Hidden address',
            'scrap_description' => 'Hidden scrap',
            'status' => PickupRequest::STATUS_PENDING_REVIEW,
            'requested_at' => now(),
        ]);

        Sanctum::actingAs($customer, ['customer']);

        $create = $this->postJson('/api/customer/pickup-requests', [
            'pickup_address' => '42 Green Street, Pune',
            'location_notes' => 'Near main gate',
            'scrap_description' => 'Paper and plastic',
            'estimated_weight_mt' => 0.25,
            'preferred_pickup_date' => now()->addDay()->toDateString(),
            'notes' => 'Call before arrival',
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.status', PickupRequest::STATUS_PENDING_REVIEW)
            ->assertJsonPath('data.pickup_address', '42 Green Street, Pune');

        $this->getJson('/api/customer/pickup-requests')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.customer_email', $customer->email);
    }

    public function test_pickup_request_creation_validates_required_fields(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'phone' => '1111111111']);
        Sanctum::actingAs($customer, ['customer']);

        $this->postJson('/api/customer/pickup-requests', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['pickup_address', 'scrap_description']);
    }

    public function test_completed_collection_job_updates_customer_visible_history(): void
    {
        Storage::fake('local');

        $customer = User::factory()->create(['role' => 'customer', 'phone' => '1111111111']);
        $vendor = User::factory()->create(['role' => 'vendor', 'phone' => '2222222222']);
        $godown = Godown::create([
            'vendor_id' => $vendor->id,
            'name' => 'Central Godown',
            'location' => 'Pune',
            'address' => 'Industrial Area, Pune',
            'capacity_limit_mt' => 100,
            'current_stock_mt' => 10,
        ]);
        $pickupRequest = PickupRequest::create([
            'user_id' => $customer->id,
            'assigned_vendor_id' => $vendor->id,
            'assigned_godown_id' => $godown->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'pickup_address' => '42 Green Street, Pune',
            'scrap_description' => 'Metal scrap',
            'status' => PickupRequest::STATUS_ASSIGNED,
            'requested_at' => now(),
        ]);
        $job = CollectionJob::create([
            'godown_id' => $godown->id,
            'pickup_request_id' => $pickupRequest->id,
            'status' => 'truck_dispatched',
            'dispatched_at' => now(),
        ]);

        Sanctum::actingAs($vendor, ['vendor']);

        $this->post("/api/vendor/jobs/{$job->id}/complete", [
            'collected_amount_mt' => 0.25,
            'proof_image' => UploadedFile::fake()->image('proof.jpg'),
        ], ['Accept' => 'application/json'])->assertOk();

        $pickupRequest->refresh();
        $this->assertSame(PickupRequest::STATUS_COMPLETED, $pickupRequest->status);
        $this->assertNotNull($pickupRequest->picked_up_at);

        Sanctum::actingAs($customer, ['customer']);
        $this->getJson('/api/customer/pickup-requests')
            ->assertOk()
            ->assertJsonPath('data.0.status', PickupRequest::STATUS_COMPLETED);
    }
}
