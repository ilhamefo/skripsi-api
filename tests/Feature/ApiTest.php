<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_getting_employees_no_auth()
    {
        $response = $this->getJson('/api/employees');
        $response->assertStatus(401)
            ->assertUnauthorized();
    }

    public function test_getting_list_of_employees_with_auth()
    {
        $user = User::factory()->employee()->create();

        $response = $this->actingAs($user)
            ->withSession(['user' => $user])
            ->getJson('/api/employees');

        $response->assertStatus(200);
    }
}
