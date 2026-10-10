<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthSecurityTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_send_otp_does_not_return_the_otp_code(): void
    {
        $student = $this->makeStudent();

        foreach (['send-otp', 'resend-otp'] as $endpoint) {
            $response = $this->postJson("/api/{$endpoint}", ['login' => $student->email]);

            $response->assertOk();
            $this->assertArrayNotHasKey('otp', $response->json('data') ?? [], "{$endpoint} leaks the OTP");
        }
    }

    public function test_password_cannot_be_changed_without_verifying_an_otp(): void
    {
        // A student with no pending OTP (otp = null) used to be accepted directly
        $student = $this->makeStudent();

        $this->postJson('/api/change-password', [
            'login' => $student->email,
            'password' => 'hacked123',
            'password_confirmation' => 'hacked123',
        ])->assertStatus(400);

        $this->assertTrue(Hash::check('secret123', $student->fresh()->password));
    }

    public function test_password_reset_works_after_verifying_the_otp_and_only_once(): void
    {
        $student = $this->makeStudent();

        $this->postJson('/api/send-otp', ['login' => $student->email])->assertOk();
        $otp = $student->fresh()->otp;

        $this->postJson('/api/verify-otp', ['login' => $student->email, 'otp' => (string) $otp])->assertOk();

        $this->postJson('/api/change-password', [
            'login' => $student->email,
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ])->assertOk();
        $this->assertTrue(Hash::check('newpass123', $student->fresh()->password));

        // The verification is used up: a second change needs a new OTP
        $this->postJson('/api/change-password', [
            'login' => $student->email,
            'password' => 'again1234',
            'password_confirmation' => 'again1234',
        ])->assertStatus(400);
        $this->assertTrue(Hash::check('newpass123', $student->fresh()->password));
    }

    public function test_otp_is_invalidated_after_too_many_wrong_guesses(): void
    {
        $student = $this->makeStudent();
        $this->postJson('/api/send-otp', ['login' => $student->email])->assertOk();
        $otp = (string) $student->fresh()->otp;
        $wrong = $otp === '1111' ? '2222' : '1111';

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/verify-otp', ['login' => $student->email, 'otp' => $wrong])->assertStatus(400);
        }

        // Even the right code is refused now: brute forcing the 4-digit code is not possible
        $this->postJson('/api/verify-otp', ['login' => $student->email, 'otp' => $otp])->assertStatus(400);
        $this->assertNull($student->fresh()->otp);
    }

    public function test_blocked_student_cannot_log_in(): void
    {
        $student = $this->makeStudent(['block_flag' => 1]);

        $this->postJson('/api/login', [
            'login' => $student->email,
            'password' => 'secret123',
            'device_id' => 'device-1',
        ])->assertStatus(422);

        $this->assertSame(0, $student->tokens()->count());
    }

    public function test_active_student_can_log_in_and_previous_session_is_revoked(): void
    {
        $student = $this->makeStudent();
        $student->createToken('old');

        $response = $this->postJson('/api/login', [
            'login' => $student->email,
            'password' => 'secret123',
            'device_id' => 'device-1',
        ]);

        $response->assertOk()->assertJsonStructure(['data' => ['token', 'user']]);
        $this->assertSame(1, $student->tokens()->count());
        $this->assertDatabaseHas('student_devices', ['student_id' => $student->id, 'device_id' => 'device-1']);
    }

    public function test_student_whose_grade_was_deleted_can_still_log_in(): void
    {
        $student = $this->makeStudent();
        $student->category->delete(); // soft delete from the dashboard

        $this->postJson('/api/login', [
            'login' => $student->email,
            'password' => 'secret123',
            'device_id' => 'device-1',
        ])->assertOk()->assertJsonPath('data.user.category.id', null);
    }

    public function test_login_with_wrong_password_is_rejected(): void
    {
        $student = $this->makeStudent();

        $this->postJson('/api/login', [
            'login' => $student->email,
            'password' => 'wrongpass1',
            'device_id' => 'device-1',
        ])->assertStatus(422);
    }
}
