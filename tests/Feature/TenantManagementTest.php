<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a default user for authentication in tests
        $this->user = User::factory()->create();
    }

    /**
     * Test guest cannot access dashboard.
     */
    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get(route('tenants.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test login page loads.
     */
    public function test_login_page_loads_successfully(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('Sign In');
    }

    /**
     * Test authenticating with valid credentials.
     */
    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@kost.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@kost.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('tenants.index'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test dashboard loading.
     */
    public function test_tenant_dashboard_loads_successfully(): void
    {
        $tenant = Tenant::create([
            'name' => 'John Doe',
            'phone_number' => '08123456789',
            'room_number' => '101',
            'monthly_rent' => 1500000,
            'due_day' => 10,
            'move_in_date' => '2026-08-12',
            'move_out_date' => '2026-09-12',
        ]);

        $response = $this->actingAs($this->user)->get(route('tenants.index'));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('101');
        $response->assertSee('Rp1.500.000');
        $response->assertSee('Day 10');
        $response->assertSee('12 Aug 2026');
        $response->assertSee('12 Sep 2026');
    }

    /**
     * Test creating a tenant.
     */
    public function test_can_create_tenant(): void
    {
        $data = [
            'name' => 'Jane Smith',
            'phone_number' => '08987654321',
            'room_number' => '202',
            'monthly_rent' => 2000000,
            'due_day' => 15,
            'move_in_date' => '2026-08-12',
            'months' => 1,
        ];

        $response = $this->actingAs($this->user)->post(route('tenants.store'), $data);

        $response->assertRedirect(route('tenants.index'));
        $this->assertDatabaseHas('tenants', [
            'name' => 'Jane Smith',
            'room_number' => '202',
            'move_in_date' => '2026-08-12 00:00:00',
            'move_out_date' => '2026-09-12 00:00:00',
        ]);
    }

    /**
     * Test updating a tenant.
     */
    public function test_can_update_tenant(): void
    {
        $tenant = Tenant::create([
            'name' => 'Jane Smith',
            'phone_number' => '08987654321',
            'room_number' => '202',
            'monthly_rent' => 2000000,
            'due_day' => 15,
            'move_in_date' => '2026-08-12',
            'move_out_date' => '2026-09-12',
        ]);

        $updatedData = [
            'name' => 'Jane S. Updated',
            'phone_number' => '08987654321',
            'room_number' => '202', // Keep same room
            'monthly_rent' => 2200000,
            'due_day' => 15,
            'move_in_date' => '2026-08-12',
            'move_out_date' => '2026-10-12', // Extend duration to 2 months
        ];

        $response = $this->actingAs($this->user)->put(route('tenants.update', $tenant->id), $updatedData);

        $response->assertRedirect(route('tenants.index'));
        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'name' => 'Jane S. Updated',
            'monthly_rent' => 2200000,
            'move_out_date' => '2026-10-12 00:00:00',
        ]);
    }

    /**
     * Test recording payment.
     */
    public function test_can_record_payment(): void
    {
        $tenant = Tenant::create([
            'name' => 'Mike Johnson',
            'phone_number' => '08111222333',
            'room_number' => '303',
            'monthly_rent' => 1800000,
            'due_day' => 5,
            'move_in_date' => '2026-08-12',
            'move_out_date' => '2026-09-12',
        ]);

        // Pay for 3 months
        $response = $this->actingAs($this->user)->post(route('tenants.pay', $tenant->id), ['months' => 3]);

        $response->assertRedirect(route('tenants.index'));
        
        // Assert transactions were created
        $this->assertDatabaseHas('transactions', [
            'tenant_id' => $tenant->id,
            'amount' => 1800000,
        ]);

        $this->assertEquals(3, $tenant->transactions()->whereNotNull('paid_at')->count());
        
        // Assert status turns to paid
        $this->assertEquals('paid', $tenant->fresh()->status);
        
        // Assert move_out_date is extended by 3 months from current move_out_date (September 12 -> December 12)
        $this->assertEquals('2026-12-12', $tenant->fresh()->move_out_date->format('Y-m-d'));
    }

    /**
     * Test deleting a tenant.
     */
    public function test_can_delete_tenant(): void
    {
        $tenant = Tenant::create([
            'name' => 'Mike Johnson',
            'phone_number' => '08111222333',
            'room_number' => '303',
            'monthly_rent' => 1800000,
            'due_day' => 5,
            'move_in_date' => '2026-08-12',
            'move_out_date' => '2026-09-12',
        ]);

        $response = $this->actingAs($this->user)->delete(route('tenants.destroy', $tenant->id));

        $response->assertRedirect(route('tenants.index'));
        $this->assertDatabaseMissing('tenants', [
            'id' => $tenant->id,
        ]);
    }
}
