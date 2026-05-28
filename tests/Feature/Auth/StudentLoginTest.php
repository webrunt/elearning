<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_student_can_login_on_main_domain(): void
    {
        $response = $this->post('/login', [
            'email' => 'student@elearning.local',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('my-learning.index'));
        $this->assertAuthenticatedAs(User::where('email', 'student@elearning.local')->first());
    }

    public function test_staff_cannot_login_on_student_portal(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@elearning.local',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
