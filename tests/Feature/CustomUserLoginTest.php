<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomUserLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_login_with_custom_credentials(): void
    {
        $response = $this->post('/login', [
            'email'    => 'admin@mozamart',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertEquals('admin', auth()->user()->name);
        $this->assertEquals('admin', auth()->user()->role);
        $this->assertTrue(auth()->user()->isAdmin());
    }

    public function test_owner_can_login_with_custom_credentials(): void
    {
        $response = $this->post('/login', [
            'email'    => 'owner@mozamart',
            'password' => 'ownercantik',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertEquals('owner', auth()->user()->name);
        $this->assertEquals('owner', auth()->user()->role);
        $this->assertTrue(auth()->user()->isAdmin()); // owner has admin privileges
        $this->assertTrue(auth()->user()->isOwner());
    }

    public function test_staff_can_login_with_custom_credentials(): void
    {
        $response = $this->post('/login', [
            'email'    => 'staff@mozamart',
            'password' => 'staff123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertEquals('staff', auth()->user()->name);
        $this->assertEquals('staff', auth()->user()->role);
        $this->assertTrue(auth()->user()->isStaff());
    }
}
