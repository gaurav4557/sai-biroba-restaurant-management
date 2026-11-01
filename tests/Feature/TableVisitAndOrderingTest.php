<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\TableVisit;
use App\Models\TableWaiterAssignment;
use App\Models\User;
use App\Services\DiningAndOrderService;
use Tests\TestCase;

class TableVisitAndOrderingTest extends TestCase
{
    /**
     * Unauthenticated guest is redirected to login.
     */
    public function test_guest_is_redirected_to_login_from_waiter_routes(): void
    {
        $response = $this->get('/waiter/tables/1/seat');
        $response->assertRedirect('/login');

        $response = $this->get('/waiter/visits/1');
        $response->assertRedirect('/login');

        $response = $this->get('/waiter/visits/1/order');
        $response->assertRedirect('/login');
    }

    /**
     * Non-waiter roles cannot access waiter seating or ordering endpoints.
     */
    public function test_non_waiter_roles_cannot_access_waiter_routes(): void
    {
        foreach (['admin', 'kitchen', 'counter'] as $role) {
            $user = new User([
                'id' => 9100,
                'name' => "Staff {$role}",
                'phone' => '+919999900010',
                'role' => $role,
                'is_active' => true,
            ]);

            $response = $this->actingAs($user)->get('/waiter/dashboard');
            $response->assertStatus(403);
        }
    }

    /**
     * Waiter cannot access seating screen for an unassigned table.
     */
    public function test_waiter_cannot_seat_unassigned_table(): void
    {
        $waiter = new User([
            'id' => 9301,
            'name' => 'Waiter One',
            'phone' => '+919999900030',
            'role' => 'waiter',
            'is_active' => true,
        ]);

        $table = RestaurantTable::create([
            'table_number' => 'T99_TEST',
            'seating_capacity' => 4,
            'floor_section' => 'Room 1',
            'status' => 'available',
            'is_active' => true,
        ]);

        try {
            $response = $this->actingAs($waiter)->get("/waiter/tables/{$table->id}/seat");
            $response->assertStatus(403);
        } finally {
            $table->delete();
        }
    }
}
