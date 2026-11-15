<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminIngredientPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_ingredient_page(): void
    {
        $user = User::create([
            'name' => 'Admin Tester',
            'email' => 'admin.tester@example.com',
            'password' => Hash::make('password'),
            'usertype' => 'admin',
        ]);

        $response = $this->actingAs($user, 'admin')->get('/admin/ingredients');

        $response->assertOk();
        $response->assertSee('Bahan Baku');
    }
}
