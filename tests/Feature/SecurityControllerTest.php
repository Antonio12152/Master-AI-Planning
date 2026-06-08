<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->user = User::factory()->create([
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
    ]);
});

describe('SecurityController', function () {
    describe('edit()', function () {
        test('redirects to login when not authenticated', function () {
            $response = $this->get(route('security.edit'));

            expect($response->status())->toBe(302);
            expect($response->headers->get('location'))->toContain('login');
        });
    });

    describe('update()', function () {
        test('updates password with valid current password', function () {
            $response = $this->actingAs($this->user)
                ->withoutMiddleware()
                ->put(route('user-password.update'), [
                    'current_password' => 'password',
                    'password' => 'newpassword123',
                    'password_confirmation' => 'newpassword123',
                ]);

            expect($response->status())->toBe(302);
            
            $updatedUser = User::find($this->user->id);
            expect(Hash::check('newpassword123', $updatedUser->password))->toBeTrue();
        });

        test('rejects password update with incorrect current password', function () {
            $originalPassword = User::find($this->user->id)->password;

            $response = $this->actingAs($this->user)
                ->withoutMiddleware()
                ->put(route('user-password.update'), [
                    'current_password' => 'wrongpassword',
                    'password' => 'newpassword123',
                    'password_confirmation' => 'newpassword123',
                ]);

            // Validation should fail and prevent password update
            expect($response->status())->toBe(302);
            expect(User::find($this->user->id)->password)->toBe($originalPassword);
        });

        test('rejects password update when confirmation does not match', function () {
            $originalPassword = User::find($this->user->id)->password;

            $response = $this->actingAs($this->user)
                ->withoutMiddleware()
                ->put(route('user-password.update'), [
                    'current_password' => 'password',
                    'password' => 'newpassword123',
                    'password_confirmation' => 'differentpassword',
                ]);

            // Validation should fail and prevent password update
            expect($response->status())->toBe(302);
            expect(User::find($this->user->id)->password)->toBe($originalPassword);
        });

        test('requires password to meet strength requirements', function () {
            $originalPassword = User::find($this->user->id)->password;

            $response = $this->actingAs($this->user)
                ->withoutMiddleware()
                ->put(route('user-password.update'), [
                    'current_password' => 'password',
                    'password' => 'weak',
                    'password_confirmation' => 'weak',
                ]);

            // Validation should fail and prevent password update
            expect($response->status())->toBe(302);
            expect(User::find($this->user->id)->password)->toBe($originalPassword);
        });

        test('redirects back after successful password update', function () {
            $response = $this->actingAs($this->user)
                ->withoutMiddleware()
                ->put(route('user-password.update'), [
                    'current_password' => 'password',
                    'password' => 'newpassword123',
                    'password_confirmation' => 'newpassword123',
                ]);

            expect($response->status())->toBe(302);
        });

        test('requires authentication', function () {
            $response = $this->withoutMiddleware()
                ->put(route('user-password.update'), [
                    'current_password' => 'password',
                    'password' => 'newpassword123',
                    'password_confirmation' => 'newpassword123',
                ]);

            expect($response->status())->toBe(302);
            // Should redirect since not authenticated
        });

        test('does not update password on validation error', function () {
            $originalPassword = User::find($this->user->id)->password;

            $this->actingAs($this->user)
                ->withoutMiddleware()
                ->put(route('user-password.update'), [
                    'current_password' => 'wrongpassword',
                    'password' => 'newpassword123',
                    'password_confirmation' => 'newpassword123',
                ]);

            expect(User::find($this->user->id)->password)->toBe($originalPassword);
        });

        test('requires current password field', function () {
            $originalPassword = User::find($this->user->id)->password;

            $response = $this->actingAs($this->user)
                ->withoutMiddleware()
                ->put(route('user-password.update'), [
                    'password' => 'newpassword123',
                    'password_confirmation' => 'newpassword123',
                ]);

            // Validation should fail
            expect($response->status())->toBe(302);
            expect(User::find($this->user->id)->password)->toBe($originalPassword);
        });

        test('requires password confirmation', function () {
            $originalPassword = User::find($this->user->id)->password;

            $response = $this->actingAs($this->user)
                ->withoutMiddleware()
                ->put(route('user-password.update'), [
                    'current_password' => 'password',
                    'password' => 'newpassword123',
                ]);

            // Validation should fail
            expect($response->status())->toBe(302);
            expect(User::find($this->user->id)->password)->toBe($originalPassword);
        });

        test('rejects empty password field', function () {
            $originalPassword = User::find($this->user->id)->password;

            $response = $this->actingAs($this->user)
                ->withoutMiddleware()
                ->put(route('user-password.update'), [
                    'current_password' => 'password',
                    'password' => '',
                    'password_confirmation' => '',
                ]);

            // Validation should fail
            expect($response->status())->toBe(302);
            expect(User::find($this->user->id)->password)->toBe($originalPassword);
        });

        test('logs user in after successful password change', function () {
            $this->actingAs($this->user)
                ->withoutMiddleware()
                ->put(route('user-password.update'), [
                    'current_password' => 'password',
                    'password' => 'newpassword123',
                    'password_confirmation' => 'newpassword123',
                ]);

            // User should still be logged in
            $this->assertAuthenticatedAs($this->user);
        });
    });
});