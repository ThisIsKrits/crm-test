<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\TestCase as FrameworkTestCase;
use Tests\TestCase;

class UserTest extends FrameworkTestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_user_creation()
    {
        $response = $this->postJson('/api/users', [
            'name' => 'Apple',
            'price' => 2000,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'name' => 'Apple',
            'price' => 2000,
        ]);
    }
}
