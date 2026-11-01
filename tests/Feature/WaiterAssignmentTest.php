<?php

namespace Tests\Feature;

use App\Models\RestaurantTable;
use App\Models\TableWaiterAssignment;
use App\Models\User;
use App\Services\WaiterAssignmentService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WaiterAssignmentTest extends TestCase
{
    /**
     * Unauthenticated guest is redirected to login from assignment routes.
     */
    public function test_guest_is_redirected_to_login_from_assignments(): void
    {
        $response = $this->get('/admin/assignments');
        $response->assertRedirect('/login');
    }

    /**
     * Non-admin roles (waiter, kitchen, counter) cannot access assignment management.
     */
    public function test_non_admin_roles_cannot_access_assignments(): void
    {
        foreach (['waiter', 'kitchen', 'counter'] as $role) {
            $user = new User([
                'id' => 9200,
                'name' => "Staff {$role}",
                'phone' => '+919999900010',
                'role' => $role,
                'is_active' => true,
            ]);

            $response = $this->actingAs($user)->get('/admin/assignments');
            $response->assertStatus(403);
        }
    }

    /**
     * Admin can view the assignment console with room sections.
     */
    public function test_admin_can_access_assignment_console(): void
    {
        $admin = new User([
            'id' => 9001,
            'name' => 'Admin User',
            'phone' => '+919999999000',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/assignments');
        $response->assertStatus(200);
        $response->assertSee('Waiter-to-Table Assignments');
        $response->assertSee('Room 1 &bull; Tables T1 to T10', false);
        $response->assertSee('Room 2 &bull; Tables T11 to T20', false);
    }

    /**
     * Admin dashboard links to Waiter Assignments.
     */
    public function test_admin_dashboard_links_to_assignments(): void
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
        $response->assertSee(route('admin.assignments.index'));
    }

    /**
     * Waiter dashboard shows only assigned tables for the authenticated waiter.
     */
    public function test_waiter_dashboard_shows_empty_state_when_no_tables_assigned(): void
    {
        $waiter = new User([
            'id' => 9301,
            'name' => 'Empty Waiter',
            'phone' => '+919999900030',
            'role' => 'waiter',
            'is_active' => true,
        ]);

        $response = $this->actingAs($waiter)->get('/waiter/dashboard');
        $response->assertStatus(200);
        $response->assertSee('No Tables Assigned Yet');
    }
}
