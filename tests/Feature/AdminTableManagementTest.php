<?php

namespace Tests\Feature;

use App\Models\RestaurantTable;
use App\Models\User;
use Tests\TestCase;

class AdminTableManagementTest extends TestCase
{
    /**
     * Unauthenticated guest is redirected to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/tables');
        $response->assertRedirect('/login');
    }

    /**
     * Non-admin roles (waiter, kitchen, counter) receive 403 Forbidden.
     */
    public function test_non_admin_roles_cannot_access_tables(): void
    {
        foreach (['waiter', 'kitchen', 'counter'] as $role) {
            $user = new User([
                'id' => 9100,
                'name' => "Staff {$role}",
                'phone' => '+919999900001',
                'role' => $role,
                'is_active' => true,
            ]);

            $response = $this->actingAs($user)->get('/admin/tables');
            $response->assertStatus(403);
        }
    }

    /**
     * Admin can successfully view the tables dashboard.
     */
    public function test_admin_can_access_tables_dashboard(): void
    {
        $admin = new User([
            'id' => 9001,
            'name' => 'Admin User',
            'phone' => '+919999999000',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/tables');
        $response->assertStatus(200);
        $response->assertSee('Floor Plan &amp; Table Management', false);
        $response->assertSee('Room 1 &bull; Tables T1 to T10', false);
        $response->assertSee('Room 2 &bull; Tables T11 to T20', false);
    }

    /**
     * Admin dashboard contains link to Floor & Tables.
     */
    public function test_admin_dashboard_links_to_tables(): void
    {
        $admin = new User([
            'id' => 9001,
            'name' => 'Admin User',
            'phone' => '+919999999000',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee(route('admin.tables.index'));
    }
}
