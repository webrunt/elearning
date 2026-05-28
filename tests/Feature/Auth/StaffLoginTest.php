<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_staff_can_login_on_admin_domain(): void
    {
        config(['domains.admin' => 'admin.elearning.local']);

        $response = $this->post('http://admin.elearning.local/login', [
            'email' => 'admin@elearning.local',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard', absolute: false));
        $this->assertAuthenticatedAs(User::where('email', 'admin@elearning.local')->first());
    }

    public function test_student_cannot_login_on_admin_domain(): void
    {
        config(['domains.admin' => 'admin.elearning.local']);

        $response = $this->post('http://admin.elearning.local/login', [
            'email' => 'student@elearning.local',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
