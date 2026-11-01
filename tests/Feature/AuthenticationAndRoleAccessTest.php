<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAndRoleAccessTest extends TestCase
{
    /**
     * Test login screen loads with 200 OK.
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Sai Biroba');
        $response->assertSee('Mobile Phone Number');
    }

    /**
     * Test root route redirects unauthenticated guest to login.
     */
    public function test_root_redirects_guest_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    /**
     * Test unauthenticated guest cannot access admin dashboard.
     */
    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * Test waiter receives 403 Forbidden when trying to access admin dashboard.
     */
    public function test_waiter_cannot_access_admin_dashboard(): void
    {
        $waiter = new User([
            'id' => 9991,
            'name' => 'Test Waiter',
            'phone' => '+919999999901',
            'role' => 'waiter',
            'is_active' => true,
        ]);

        $response = $this->actingAs($waiter)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    /**
     * Test user with NULL role is denied access.
     */
    public function test_user_with_null_role_is_denied_access(): void
    {
        $unassignedUser = new User([
            'id' => 9992,
            'name' => 'Pending Role User',
            'phone' => '+919999999902',
            'role' => null,
            'is_active' => true,
        ]);

        $response = $this->actingAs($unassignedUser)->get('/waiter/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * Test user with invalid role is denied access.
     */
    public function test_user_with_invalid_role_is_denied_access(): void
    {
        $invalidUser = new User([
            'id' => 9993,
            'name' => 'Hacker Role User',
            'phone' => '+919999999903',
            'role' => 'superuser',
            'is_active' => true,
        ]);

        $response = $this->actingAs($invalidUser)->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * Test deactivated user is denied access.
     */
    public function test_deactivated_user_is_denied_access(): void
    {
        $deactivatedUser = new User([
            'id' => 9994,
            'name' => 'Deactivated User',
            'phone' => '+919999999904',
            'role' => 'waiter',
            'is_active' => false,
        ]);

        $response = $this->actingAs($deactivatedUser)->get('/waiter/dashboard');
        $response->assertRedirect('/login');
    }
}
