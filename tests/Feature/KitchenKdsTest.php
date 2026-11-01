<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemStatusHistory;
use App\Models\RestaurantTable;
use App\Models\TableVisit;
use App\Models\User;
use App\Services\KitchenService;
use Tests\TestCase;

class KitchenKdsTest extends TestCase
{
    /**
     * Unauthenticated guest is redirected to login.
     */
    public function test_guest_is_redirected_to_login_from_kitchen_routes(): void
    {
        $response = $this->get('/kitchen/dashboard');
        $response->assertRedirect('/login');

        $response = $this->post('/kitchen/orders/1/start-prep');
        $response->assertRedirect('/login');
    }

    /**
     * Non-kitchen roles cannot access kitchen dashboard or mutation actions.
     */
    public function test_non_kitchen_roles_cannot_access_kitchen_routes(): void
    {
        foreach (['admin', 'waiter', 'counter'] as $role) {
            $user = new User([
                'id' => 9500,
                'name' => "Staff {$role}",
                'phone' => '+919999900050',
                'role' => $role,
                'is_active' => true,
            ]);

            $response = $this->actingAs($user)->get('/kitchen/dashboard');
            $response->assertStatus(403);
        }
    }

    /**
     * Kitchen staff can view the KDS dashboard.
     */
    public function test_kitchen_staff_can_view_dashboard(): void
    {
        $chef = new User([
            'id' => 9501,
            'name' => 'Chef Vikram',
            'phone' => '+919999900051',
            'role' => 'kitchen',
            'is_active' => true,
        ]);

        $response = $this->actingAs($chef)->get('/kitchen/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Kitchen Order Queue');
        $response->assertSee('Live Kitchen Display System (KDS)');
    }
}
